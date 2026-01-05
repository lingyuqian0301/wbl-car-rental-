<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Invoice;
use App\Mail\BookingInvoiceMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\View\View;

class AdminPaymentController extends Controller
{
    /**
     * Display a listing of all payments.
     */
    public function index(Request $request): View
    {
        $query = Payment::with(['booking.customer', 'booking.vehicle']);
        
        $filterPaymentStatus = $request->get('filter_payment_status');
        $filterBookingStatus = $request->get('filter_booking_status');
        
        // Filter by payment status (Full, Deposit, Balance)
        if ($filterPaymentStatus) {
            if ($filterPaymentStatus === 'Full') {
                $query->where(function($q) {
                    $q->where('payment_status', 'Full')
                      ->orWhere('isPayment_complete', true);
                });
            } elseif ($filterPaymentStatus === 'Deposit') {
                $query->where('isPayment_complete', false)
                      ->where('payment_status', '!=', 'Full');
            } elseif ($filterPaymentStatus === 'Balance') {
                $query->where('payment_status', 'Verified')
                      ->where('isPayment_complete', false);
            }
        }
        
        // Filter by booking status
        if ($filterBookingStatus) {
            $query->whereHas('booking', function($q) use ($filterBookingStatus) {
                $q->where('booking_status', $filterBookingStatus);
            });
        }
        
        // Sort by payment date desc (default)
        $payments = $query->orderBy('payment_date', 'desc')
            ->orderBy('paymentID', 'desc')
            ->paginate(20)->withQueryString();

        // Summary stats for header
        $today = Carbon::today();
        $totalPayments = Payment::count();
        $totalPending = Payment::where('payment_status', 'Pending')->count();
        $totalVerified = Payment::where('payment_status', 'Verified')->count();
        $totalFullPayment = Payment::where('payment_status', 'Full')
            ->orWhere(function($q) {
                $q->where('payment_status', 'Verified')
                  ->whereHas('booking', function($bookingQuery) {
                      $bookingQuery->whereRaw('(SELECT COALESCE(SUM(total_amount), 0) FROM payment WHERE payment.bookingID = booking.bookingID AND payment_status IN ("Verified", "Full")) >= booking.rental_amount + booking.deposit_amount');
                  });
            })->count();
        $totalToday = Payment::whereDate('payment_date', $today)->count();
        
        // Get booking statuses for filter
        $bookingStatuses = Booking::distinct()->pluck('booking_status')->filter()->sort()->values();

        return view('admin.payments.index', [
            'payments' => $payments,
            'filterPaymentStatus' => $filterPaymentStatus,
            'filterBookingStatus' => $filterBookingStatus,
            'bookingStatuses' => $bookingStatuses,
            'totalPayments' => $totalPayments,
            'totalPending' => $totalPending,
            'totalVerified' => $totalVerified,
            'totalFullPayment' => $totalFullPayment,
            'totalToday' => $totalToday,
            'today' => $today,
        ]);
    }

    public function show($id)
    {
        // Find the payment and load related booking/customer info
        $payment = \App\Models\Payment::with(['booking.customer', 'booking.vehicle'])
                    ->findOrFail($id);

        return view('admin.payments.show', compact('payment'));
    }

    /**
     * Approve a payment, generate an invoice record, and send the Gmail.
     */
public function approve($id): \Illuminate\Http\RedirectResponse
    {
        // 1. Load Payment, Booking, and Customer (with User for email)
        $payment = \App\Models\Payment::with(['booking.vehicle', 'booking.customer.user'])->findOrFail($id);
        $booking = $payment->booking;

        // 2. Update Payment Status to Verified
        $payment->update([
            'payment_status'   => 'Verified',
            'payment_isVerify' => true,
            'latest_Update_Date_Time' => now(),
        ]);

        // 3. Update Booking Status
        if ($booking) {
            $booking->update(['booking_status' => 'Confirmed']);
        }

        // 4. Create Invoice
        $amountForInvoice = $booking->total_amount ?? $booking->rental_amount ?? 0;
        $invoiceData = \App\Models\Invoice::firstOrCreate(
            ['bookingID' => $booking->bookingID],
            [
                'invoice_number' => 'INV-' . date('Ymd') . '-' . $booking->bookingID,
                'issue_date'     => now(),
                'totalAmount'    => $amountForInvoice, 
            ]
        );

        // 5. AUTOMATIC WALLET DEDUCTION (No Transaction History)
        try {
            $wallet = \Illuminate\Support\Facades\DB::table('walletaccount')
                ->where('customerID', $booking->customerID)
                ->first();

            if ($wallet) {
                // Calculate new outstanding amount
                // Logic: Current Outstanding - Paid Amount
                // ensure it doesn't go below zero
                $paymentAmount = $payment->total_amount ?? 0;
                $newOutstanding = max(0, $wallet->outstanding_amount - $paymentAmount);

                \Illuminate\Support\Facades\DB::table('walletaccount')
                    ->where('walletAccountID', $wallet->walletAccountID)
                    ->update([
                        'outstanding_amount'          => $newOutstanding,
                        'wallet_lastUpdate_Date_Time' => now()
                    ]);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Wallet Logic Error: ' . $e->getMessage());
        }

        // 6. Send Email (Invoice)
        $recipientEmail = $booking->customer->user->email ?? null;
        if ($recipientEmail) {
            try {
                $rentalAmount = $booking->rental_amount;
                $depositAmount = $booking->deposit_amount;
                $totalPaid = $booking->payments()->where('payment_status', 'Verified')->sum('total_amount');
                
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice', compact('booking', 'invoiceData', 'rentalAmount', 'depositAmount', 'totalPaid'));
                
                \Illuminate\Support\Facades\Mail::to($recipientEmail)
                    ->send(new \App\Mail\BookingInvoiceMail($booking, $pdf));
            } catch (\Exception $e) {
                // If email fails, don't stop the process
            }
        }

        return redirect()->route('admin.payments.index')
            ->with('success', 'Payment Verified. Wallet Balance Updated.');
    }

    /**
     * Reject a payment and provide a reason.
     */
    public function reject($id): RedirectResponse
    {
        $payment = Payment::where('paymentID', $id)->firstOrFail();

        // FIX: Changed 'status' to 'payment_status' and removed invalid columns
        $payment->update([
            'payment_status' => 'Rejected',
            // 'rejected_reason' => 'Receipt rejected', // Removed as column doesn't exist
            'latest_Update_Date_Time' => now(),
        ]);

        return redirect()
            ->route('admin.payments.index')
            ->with('success', 'Payment rejected.');
    }

    /**
     * Update payment verification status.
     */
 public function updateVerify(Request $request, $id): \Illuminate\Http\RedirectResponse
    {
        $payment = \App\Models\Payment::with(['booking.customer'])->findOrFail($id);
        $booking = $payment->booking;
        
        $isVerify = $request->input('payment_isVerify') == '1' || $request->input('payment_isVerify') === true;
        
        // 1. Prepare Update Data
        $updateData = [
            'payment_isVerify' => $isVerify,
            'latest_Update_Date_Time' => \Carbon\Carbon::now(),
        ];
        
        if ($isVerify) {
            // FIX: If verified, set status to 'Verified' (or 'Full' if complete)
            // This ensures the Customer sees "Paid" immediately
            $updateData['payment_status'] = 'Verified';
            
            // 2. Generate Invoice (if missing)
            \App\Models\Invoice::firstOrCreate(
                ['bookingID' => $booking->bookingID],
                [
                    'invoice_number' => 'INV-' . date('Ymd') . '-' . $booking->bookingID,
                    'issue_date'     => now(),
                    'totalAmount'    => $booking->total_amount ?? $booking->rental_amount,
                ]
            );

            // 3. Update Booking Status
            // FIX: Force Booking to 'Confirmed' if payment is verified.
            // (Previously it only confirmed if payment was 100% full)
            if ($booking) {
                $booking->update(['booking_status' => 'Confirmed']);
            }

            // 4. WALLET DEDUCTION LOGIC (Added to match Approve function)
            try {
                $wallet = \Illuminate\Support\Facades\DB::table('walletaccount')
                    ->where('customerID', $booking->customerID)
                    ->first();

                if ($wallet) {
                    $paymentAmount = $payment->total_amount ?? 0;
                    $newOutstanding = max(0, $wallet->outstanding_amount - $paymentAmount);

                    \Illuminate\Support\Facades\DB::table('walletaccount')
                        ->where('walletAccountID', $wallet->walletAccountID)
                        ->update([
                            'outstanding_amount'          => $newOutstanding,
                            'wallet_lastUpdate_Date_Time' => now()
                        ]);
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Wallet Update Failed: ' . $e->getMessage());
            }

        } else {
            // If un-verifying, revert status
            $updateData['payment_status'] = 'Pending';
            $updateData['isPayment_complete'] = false;
        }
        
        $payment->update($updateData);
        
        return redirect()->route('admin.payments.index')
            ->with('success', 'Payment verification updated successfully.');
    }

    /**
     * Manually generate and download a PDF invoice from the admin panel.
     */
    public function generateInvoice($id)
    {
        $payment = Payment::where('paymentID', $id)->firstOrFail();
        $booking = $payment->booking;
        
        // Find existing invoice record for the PDF data
        $invoiceData = Invoice::where('bookingID', $booking->bookingID)->first();

        $rentalAmount = $booking->rental_amount;
        $depositAmount = $booking->deposit_amount;
        $totalPaid = $booking->payments->where('payment_status', 'Verified')->sum('total_amount');

        $pdf = Pdf::loadView('pdf.invoice', compact('booking', 'invoiceData', 'rentalAmount', 'depositAmount', 'totalPaid'));
        return $pdf->download('Invoice-'.$booking->bookingID.'.pdf');
    }
}