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
    public function index(): View
    {
        $payments = Payment::with(['booking.customer', 'booking.vehicle'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.payments.index', [
            'payments' => $payments,
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
                $newOutstanding = max(0, $wallet->outstanding_amount - $payment->amount);

                \Illuminate\Support\Facades\DB::table('walletaccount')
                    ->where('walletAccountID', $wallet->walletAccountID)
                    ->update([
                        'outstanding_amount'   => $newOutstanding,
                        'last_update_datetime' => now()
                    ]);

                // 2. Record Transaction
                \Illuminate\Support\Facades\DB::table('wallettransaction')->insert([
                    'amount'           => $payment->amount,
                    'transaction_type' => 'Payment Verified',
                    'description'      => 'Payment verified for Booking #' . $booking->bookingID,
                    'reference_type'   => 'Booking',
                    'reference_id'     => $booking->bookingID,
                    'transaction_date' => now(),
                    'walletAccountID'  => $wallet->walletAccountID,
                    'paymentID'        => $payment->paymentID
                ]);
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
     * Manually generate and download a PDF invoice from the admin panel.
     */
    public function generateInvoice($id)
    {
        $payment = Payment::where('paymentID', $id)->firstOrFail();
        $booking = $payment->booking;
        
        // Find existing invoice record for the PDF data
        $invoiceData = Invoice::where('bookingID', $booking->bookingID)->first();

        $pdf = Pdf::loadView('pdf.invoice', compact('booking', 'invoiceData'));
        return $pdf->download('Invoice-'.$booking->bookingID.'.pdf');
    }
}
