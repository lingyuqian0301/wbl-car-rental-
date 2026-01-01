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
        $payment = Payment::where('paymentID', $id)->firstOrFail();
        
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
        // using firstOrCreate to prevent duplicates if clicked twice
        $invoiceData = Invoice::firstOrCreate(
            ['bookingID' => $booking->bookingID],
            [
                'invoice_number' => 'INV-' . date('Ymd') . '-' . $booking->bookingID,
                'issue_date'     => now(),
                'totalAmount'    => $booking->total_price,
                'staffID'        => Auth::id(),
            ]
        );

        // =========================================================
        // STEP 3: LOYALTY LOGIC (Stamps + Vouchers)
        // =========================================================
        try {
            $start = Carbon::parse($booking->start_date);
            $end   = Carbon::parse($booking->end_date);
            $hours = $start->diffInHours($end);

            // Log for debugging
            \Log::info("Loyalty Check: Booking #{$booking->bookingID} is {$hours} hours long.");

            // Rule: Booking must be at least 9 hours to earn stamps
            if ($hours >= 9) {
                $stamps = floor($hours / 3); // 1 stamp per 3 hours

                // 3a. Find or Create Loyalty Card
                $card = DB::table('loyaltycard')->where('customerID', $booking->customerID)->first();

                if ($card) {
                    // Update existing card
                    DB::table('loyaltycard')
                        ->where('loyaltyCardID', $card->loyaltyCardID)
                        ->update([
                            'total_stamps' => $card->total_stamps + $stamps,
                            'last_updated' => now() // <--- Your specific column
                        ]);
                    // Refresh data to get the new total
                    $card = DB::table('loyaltycard')->where('loyaltyCardID', $card->loyaltyCardID)->first();
                } else {
                    // Create new card
                    $newId = DB::table('loyaltycard')->insertGetId([
                        'customerID'   => $booking->customerID,
                        'total_stamps' => $stamps,
                        'last_updated' => now()
                    ], 'loyaltyCardID');
                    $card = DB::table('loyaltycard')->where('loyaltyCardID', $newId)->first();
                }

                // 3b. VOUCHER LOGIC: Check if they reached 48 stamps
                if ($card->total_stamps >= 48) {
                    // Create the Voucher
                    DB::table('voucher')->insert([
                        'loyaltyCardID' => $card->loyaltyCardID,        // Linked to Loyalty Card
                        'discount_type' => '1 Free Day (Mon-Fri)',      // Reward Description
                        'isActive'      => 1,                           // Set as Active
                        'created_at'    => now()
                    ]);

                    // Deduct the 48 stamps used
                    DB::table('loyaltycard')
                        ->where('loyaltyCardID', $card->loyaltyCardID)
                        ->decrement('total_stamps', 48);
                    
                    \Log::info("Voucher generated for Customer #{$booking->customerID}");
                }
            }
        } catch (\Exception $e) {
            // Log error but continue so the invoice still sends
            \Log::warning('Loyalty Logic Error: ' . $e->getMessage());
        }

        // =========================================================
        // STEP 4: WALLET LOGIC (Deduct Outstanding Amount)
        // =========================================================
        try {
            $wallet = DB::table('walletaccount')
                ->where('customerID', $booking->customerID)
                ->first();

            if ($wallet) {
                // Only process if there is an outstanding debt > 0
                if ($wallet->outstanding_amount > 0) {
                    $paidAmount = $payment->amount;
                    $newOutstanding = $wallet->outstanding_amount - $paidAmount;

                    // Prevent negative outstanding (optional safety check)
                    if ($newOutstanding < 0) $newOutstanding = 0;

                    // Update Wallet Balance
                    DB::table('walletaccount')
                        ->where('walletAccountID', $wallet->walletAccountID)
                        ->update([
                            'outstanding_amount'   => $newOutstanding,
                            'last_update_datetime' => now() // <--- Your specific column
                        ]);
                    
                    \Log::info("Wallet: Outstanding reduced for Wallet #{$wallet->walletAccountID}");
                }

                // Always Record Transaction History
                DB::table('wallettransaction')->insert([
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
            \Log::warning('Wallet Logic Error: ' . $e->getMessage());
        }

        // =========================================================
        // STEP 5: GENERATE PDF & SEND EMAIL
        // =========================================================
        // We generate the PDF variable here so it is NOT null when passed to Mail
        $pdf = Pdf::loadView('pdf.invoice', compact('booking', 'invoiceData'));

        try {
            Mail::to($booking->customer->email)->send(new BookingInvoiceMail($booking, $pdf));
        } catch (\Exception $e) {
            // If email fails, redirect with error but keep the database changes
            return redirect()->route('admin.payments.index')
                ->with('error', 'Payment Verified, but Gmail failed: ' . $e->getMessage());
        }

        return redirect()->route('admin.payments.index')
            ->with('success', 'Payment verified, Wallet & Loyalty updated, and Invoice emailed.');
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
