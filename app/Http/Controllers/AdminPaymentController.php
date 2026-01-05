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
                // Full payment: payment_status is 'Full' OR isPayment_complete is true
                $query->where(function($q) {
                    $q->where('payment_status', 'Full')
                      ->orWhere('isPayment_complete', true);
                });
            } elseif ($filterPaymentStatus === 'Deposit') {
                // Deposit: payments where isPayment_complete is false (deposit payments)
                $query->where('isPayment_complete', false)
                      ->where('payment_status', '!=', 'Full');
            } elseif ($filterPaymentStatus === 'Balance') {
                // Balance: verified payments that are not complete (balance/partial payments)
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
public function approve($id): RedirectResponse
    {
        // =========================================================
        // STEP 1: VERIFY PAYMENT & BOOKING
        // =========================================================
        $payment = \App\Models\Payment::where('paymentID', $id)->firstOrFail();
        $payment = \App\Models\Payment::with(['booking.vehicle', 'booking.customer'])->findOrFail($id);
        $booking = $payment->booking;
        // Update Payment Status
        $payment->update([
            'status'      => 'Verified',
            'verified_by' => Auth::id(),
        ]);

        // Update Booking Status
        $booking = $payment->booking;
        $booking->update(['booking_status' => 'Confirmed']);

        // =========================================================
        // STEP 2: CREATE INVOICE RECORD
        // =========================================================
        $invoiceData = \App\Models\Invoice::firstOrCreate(
            ['bookingID' => $booking->bookingID],
            [
                'invoice_number' => 'INV-' . date('Ymd') . '-' . $booking->bookingID,
                'issue_date'     => now(),
                // FIX: Changed 'total_price' to 'total_amount' to match your DB
                'totalAmount'    => $booking->total_amount, 
                'staffID'        => Auth::id(),
            ]
        );

        // =========================================================
        // STEP 3: LOYALTY LOGIC (Stamps + Vouchers)
        // =========================================================
        try {
            $start = \Carbon\Carbon::parse($booking->start_date);
            $end   = \Carbon\Carbon::parse($booking->end_date);
            $hours = $start->diffInHours($end);

            if ($hours >= 9) {
                $stamps = floor($hours / 3);

                $card = \Illuminate\Support\Facades\DB::table('loyaltycard')->where('customerID', $booking->customerID)->first();

                if ($card) {
                    \Illuminate\Support\Facades\DB::table('loyaltycard')
                        ->where('loyaltyCardID', $card->loyaltyCardID)
                        ->update([
                            'total_stamps' => $card->total_stamps + $stamps,
                            'last_updated' => now()
                        ]);
                    $card = \Illuminate\Support\Facades\DB::table('loyaltycard')->where('loyaltyCardID', $card->loyaltyCardID)->first();
                } else {
                    $newId = \Illuminate\Support\Facades\DB::table('loyaltycard')->insertGetId([
                        'customerID'   => $booking->customerID,
                        'total_stamps' => $stamps,
                        'last_updated' => now()
                    ], 'loyaltyCardID');
                    $card = \Illuminate\Support\Facades\DB::table('loyaltycard')->where('loyaltyCardID', $newId)->first();
                }

                // Check Reward
                if ($card->total_stamps >= 48) {
                    \Illuminate\Support\Facades\DB::table('voucher')->insert([
                        'loyaltyCardID' => $card->loyaltyCardID,
                        'discount_type' => '1 Free Day (Mon-Fri)',
                        'isActive'      => 1,
                    ]);
                    \Illuminate\Support\Facades\DB::table('loyaltycard')
                        ->where('loyaltyCardID', $card->loyaltyCardID)
                        ->decrement('total_stamps', 48);
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Loyalty Logic Error: ' . $e->getMessage());
        }

        // =========================================================
        // STEP 4: WALLET LOGIC (Deduct Outstanding Amount)
        // =========================================================
        try {
            $wallet = \Illuminate\Support\Facades\DB::table('walletaccount')
                ->where('customerID', $booking->customerID)
                ->first();

            if ($wallet) {
                // 1. Deduct from Outstanding
                // We use MAX(0, ...) to ensure it never goes negative
                $paymentAmount = $payment->total_amount ?? $payment->amount ?? 0;
                $newOutstanding = max(0, $wallet->outstanding_amount - $paymentAmount);

                \Illuminate\Support\Facades\DB::table('walletaccount')
                    ->where('walletAccountID', $wallet->walletAccountID)
                    ->update([
                        'outstanding_amount'   => $newOutstanding,
                        'last_update_datetime' => now()
                    ]);

                // 2. Record Transaction (if table exists)
                try {
                    \Illuminate\Support\Facades\DB::table('wallettransaction')->insert([
                        'amount'           => $paymentAmount,
                        'transaction_type' => 'Payment Verified',
                        'description'      => 'Payment verified for Booking #' . $booking->bookingID,
                        'reference_type'   => 'Booking',
                        'reference_id'     => $booking->bookingID,
                        'transaction_date' => now(),
                        'walletAccountID'  => $wallet->walletAccountID,
                        'paymentID'        => $payment->paymentID
                    ]);
                } catch (\Exception $e) {
                    // Table doesn't exist, skip transaction recording
                    \Illuminate\Support\Facades\Log::warning('WalletTransaction table not found: ' . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Wallet Logic Error: ' . $e->getMessage());
        }

        // =========================================================
        // STEP 5: EMAIL
        // =========================================================
        $pdf = Pdf::loadView('pdf.invoice', compact('booking', 'invoiceData'));

        try {
            Mail::to($booking->customer->email)->send(new \App\Mail\BookingInvoiceMail($booking, $pdf));
        } catch (\Exception $e) {
            return redirect()->route('admin.payments.index')
                ->with('error', 'Verified, but Email failed: ' . $e->getMessage());
        }

        return redirect()->route('admin.payments.index')
            ->with('success', 'Payment verified! Wallet, Loyalty & Invoice updated.');
    }
    /**
     * Reject a payment and provide a reason.
     */
    public function reject($id): RedirectResponse
    {
        $payment = Payment::where('paymentID', $id)->firstOrFail();

        $payment->update([
            'status' => 'Rejected',
            'verified_by' => Auth::id(),
            'rejected_reason' => 'Receipt rejected by Admin. Please upload a clear copy.',
        ]);

        return redirect()
            ->route('admin.payments.index')
            ->with('success', 'Payment rejected. Customer has been notified.');
    }

    /**
     * Update payment verification status.
     */
    public function updateVerify(Request $request, $id): RedirectResponse
    {
        $payment = Payment::findOrFail($id);
        $booking = $payment->booking;
        
        $isVerify = $request->input('payment_isVerify') == '1' || $request->input('payment_isVerify') === true;
        
        // Calculate if this is a full payment
        $totalRequired = ($booking->rental_amount ?? 0) + ($booking->deposit_amount ?? 0);
        $paidAmount = $payment->total_amount ?? 0;
        $isFullPayment = $paidAmount >= $totalRequired;
        
        $updateData = [
            'payment_isVerify' => $isVerify,
            'latest_Update_Date_Time' => Carbon::now(),
        ];
        
        // Add verify_by if field exists
        if (Schema::hasColumn('payment', 'verify_by')) {
            $updateData['verify_by'] = $isVerify ? Auth::id() : null;
        }
        
        // If payment is verified and it's a full payment, update payment status and booking status
        if ($isVerify && $isFullPayment) {
            $updateData['payment_status'] = 'Full';
            $updateData['isPayment_complete'] = true;
            
            // Update booking status to Confirmed
            $booking->update(['booking_status' => 'Confirmed']);
        } elseif ($isVerify) {
            // If verified but not full payment, set status to Verified
            $updateData['payment_status'] = 'Verified';
        } else {
            // If unverified, set status back to Pending
            $updateData['payment_status'] = 'Pending';
            $updateData['isPayment_complete'] = false;
        }
        
        $payment->update($updateData);
        
        return redirect()->route('admin.payments.index')
            ->with('success', 'Payment verification status updated successfully.');
    }

    /**
     * Manually generate and download a PDF invoice from the admin panel.
     * Creates invoice record if it doesn't exist.
     */
    public function generateInvoice($id)
    {
        $payment = Payment::where('paymentID', $id)->firstOrFail();
        $booking = $payment->booking;
        
        // Create invoice record if it doesn't exist
        $invoiceData = Invoice::firstOrCreate(
            ['bookingID' => $booking->bookingID],
            [
                'invoice_number' => 'INV-' . date('Ymd') . '-' . $booking->bookingID,
                'issue_date' => now(),
                'totalAmount' => ($booking->rental_amount ?? 0) + ($booking->deposit_amount ?? 0),
            ]
        );

        $pdf = Pdf::loadView('pdf.invoice', compact('booking', 'invoiceData'));
        return $pdf->download('Invoice-'.$booking->bookingID.'.pdf');
    }
}
