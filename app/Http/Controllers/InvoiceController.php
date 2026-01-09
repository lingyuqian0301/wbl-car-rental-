<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class InvoiceController extends Controller
{
    public function generatePDF(int $bookingId): Response
    {
        // Load Booking with relations
        // We load 'customer.user' because Name/Email are in the User table, Phone is in Customer table
        $booking = Booking::with(['customer.user', 'vehicle', 'payments', 'invoice'])
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

        // 3. GET VOUCHER (If any) - Get voucher through voucher_usage table
        $voucherUsage = \App\Models\VoucherUsage::where('bookingID', $booking->bookingID)->with('voucher')->first();
        $voucher = $voucherUsage ? $voucherUsage->voucher : null;

        // 4. CALCULATE TOTALS (Using new DB column 'total_amount')
        $verifiedPayments = $booking->payments()
            ->where('payment_status', 'Verified')
            ->get();

        // FIX: db_new uses 'total_amount', not 'amount'
        $totalPaid = $verifiedPayments->sum('total_amount');

        // Setup Booking Financials (Assuming these exist on your Booking table)
        $depositAmount = $booking->deposit_amount ?? 0;
        $rentalAmount  = $booking->rental_amount ?? ($booking->total_price - $depositAmount);

        // Prepare data for PDF
        $data = [
            'booking'       => $booking,
            'customer'      => $customer,
            'user'          => $user,
            'voucher'       => $voucher,
            'depositAmount' => $depositAmount,
            'rentalAmount'  => $rentalAmount,
            'totalPaid'     => $totalPaid,
            'invoiceData'   => $booking->invoice,
            'invoiceDate'   => now(),
        ];

        try {
            $pdf = Pdf::loadView('pdf.invoice', $data);
            return $pdf->download('Invoice-'.$booking->bookingID.'.pdf');
        } catch (\Exception $e) {
            \Log::error('PDF Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to generate invoice.');
        }
    }
}
