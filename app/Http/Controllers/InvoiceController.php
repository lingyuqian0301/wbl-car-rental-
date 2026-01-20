<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    public function generatePDF(int $bookingId)
    {
        // Load Booking with relations
        // We load 'customer.user' because Name/Email are in the User table, Phone is in Customer table
        $booking = Booking::with([
            'customer.user', 
            'vehicle.car', 
            'vehicle.motorcycle', 
            'payments', 
            'invoice'
        ])
            ->findOrFail($bookingId);

        // 1. CHECK PAYMENT STATUS (Using new DB column 'payment_status')
        $verifiedPayment = $booking->payments()
            ->where('payment_status', 'Verified') // Matches db_new (1).sql
            ->exists();

        // If you want to block downloading before payment, uncomment this:
        /*
        if (!$verifiedPayment) {
            return redirect()->back()->with('error', 'Payment not yet verified.');
        }
        */

        // 2. GET CUSTOMER DETAILS
        // In your new DB, phone is in 'customer', but name/email are likely in 'user'
        $customer = $booking->customer;
        $user = $customer ? $customer->user : null;

        // 3. GET VOUCHER (If any) - Check if voucher was used for this booking
        // Since vouchers are deactivated when used, we need to check through loyalty card
        $voucher = null;
        $discountAmount = 0;
        if ($customer) {
            $loyaltyCard = \Illuminate\Support\Facades\DB::table('loyaltycard')
                ->where('customerID', $customer->customerID)
                ->first();
            
            // Try to find voucher that was used (check deactivated vouchers)
            // NOTE: Voucher table doesn't have created_at column
            if ($loyaltyCard) {
                $usedVoucher = \Illuminate\Support\Facades\DB::table('voucher')
                    ->where('loyaltyCardID', $loyaltyCard->loyaltyCardID)
                    ->where('voucher_isActive', 0)
                    ->orderBy('voucherID', 'desc')
                    ->first();
                
                if ($usedVoucher) {
                    $voucher = $usedVoucher;
                }
            }
        }

        // 4. CALCULATE DETAILED TOTALS
        $verifiedPayments = $booking->payments()
            ->where('payment_status', 'Verified')
            ->orderBy('payment_date', 'asc')
            ->get();

        $allPayments = $booking->payments()
            ->orderBy('payment_date', 'asc')
            ->get();

        $totalPaid = $verifiedPayments->sum('total_amount');

        // Calculate breakdown
        $vehicle = $booking->vehicle;
        $dailyRate = $vehicle->rental_price ?? 0;
        $rentalBase = $dailyRate * ($booking->duration ?? 1);
        
        // Calculate addons breakdown
        $addonPrices = ['power_bank' => 5, 'phone_holder' => 5, 'usb_wire' => 3];
        $addonNames = ['power_bank' => 'Power Bank', 'phone_holder' => 'Phone Holder', 'usb_wire' => 'USB Wire'];
        $addonsArray = !empty($booking->addOns_item) ? explode(',', $booking->addOns_item) : [];
        $addonsBreakdown = [];
        $addonsTotal = 0;
        
        foreach ($addonsArray as $addon) {
            if (isset($addonPrices[$addon])) {
                $addonPrice = $addonPrices[$addon];
                $addonTotal = $addonPrice * ($booking->duration ?? 1);
                $addonsBreakdown[] = [
                    'name' => $addonNames[$addon],
                    'daily_price' => $addonPrice,
                    'duration' => $booking->duration ?? 1,
                    'total' => $addonTotal
                ];
                $addonsTotal += $addonTotal;
            }
        }

        // Get pickup surcharge (if stored in booking or calculate from rental_amount)
        // Since rental_amount is final total, we need to reverse calculate
        $depositAmount = $booking->deposit_amount ?? 50;
        $pickupSurcharge = 0; // Default, will be calculated if needed
        $returnSurcharge = 0; // Default
        $pickupCustomLocation = null;
        $returnCustomLocation = null;
        
        // Calculate base amount before discount
        $baseAmount = $rentalBase + $addonsTotal;
        
        // If voucher was used, calculate discount
        if ($voucher && $voucher->discount_type === 'PERCENT') {
            $discountAmount = $baseAmount * ($voucher->discount_amount / 100);
        } elseif ($voucher && $voucher->discount_type === 'FLAT') {
            $discountAmount = min($voucher->discount_amount, $baseAmount);
        }
        
        // Calculate final amounts
        $subtotalAfterDiscount = $baseAmount - $discountAmount;
        $finalTotal = $subtotalAfterDiscount + $depositAmount;
        $outstandingBalance = max(0, $finalTotal - $totalPaid);

        // Get customer identity details
        $localCustomer = \App\Models\Local::where('customerID', $customer->customerID)->first();
        $internationalCustomer = \App\Models\International::where('customerID', $customer->customerID)->first();
        $localstudent = $localCustomer ? ($customer->localStudent ?? null) : null; // Fix: define $localstudent variable

        // Ensure we always have invoice data for the view
        $invoiceData = $booking->invoice ?? new Invoice([
            'invoice_number' => 'INV-' . date('Ymd') . '-' . $booking->bookingID,
            'issue_date'     => now(),
            'totalAmount'    => $finalTotal,
            'bookingID'      => $booking->bookingID,
        ]);

        // Prepare comprehensive data for PDF
        $data = [
            'booking'           => $booking,
            'customer'          => $customer,
            'user'              => $user,
            'vehicle'           => $vehicle,
            'voucher'           => $voucher,
            'discountAmount'    => $discountAmount,
            'depositAmount'     => $depositAmount,
            'rentalBase'         => $rentalBase,
            'dailyRate'          => $dailyRate,
            'addonsBreakdown'    => $addonsBreakdown,
            'addonsTotal'        => $addonsTotal,
            'pickupSurcharge'    => $pickupSurcharge,
            'baseAmount'        => $baseAmount,
            'subtotalAfterDiscount' => $subtotalAfterDiscount,
            'finalTotal'         => $finalTotal,
            'totalPaid'          => $totalPaid,
            'outstandingBalance' => $outstandingBalance,
            'allPayments'        => $allPayments,
            'verifiedPayments'   => $verifiedPayments,
            'localCustomer'      => $localCustomer,
            'localstudent'       => $localstudent, // Fix: pass $localstudent to view
            'internationalCustomer' => $internationalCustomer,
            'invoiceData'        => $invoiceData,
            'invoiceDate'        => now(),
            'returnSurcharge'    => $returnSurcharge,
            'pickupCustomLocation' => $pickupCustomLocation,
            'returnCustomLocation' => $returnCustomLocation,
        ];

        try {
            $pdf = Pdf::loadView('pdf.invoice', $data);
            // Stream so it opens in a new browser tab instead of forcing download
            return $pdf->stream('Invoice-'.$booking->bookingID.'.pdf');
        } catch (\Exception $e) {
            \Log::error('PDF Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to generate invoice.');
        }
    }
}
