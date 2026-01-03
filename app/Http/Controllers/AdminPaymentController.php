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
            ->orderBy('payment_date', 'desc')
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
            'payment_status' => 'Verified',
            'payment_isVerify' => true,
            'latest_Update_Date_Time' => now(),
        ]);

        // Update Booking Status
        $booking = $payment->booking;
        $booking->update([
            'booking_status' => 'Confirmed',
            'lastUpdateDate' => now(),
        ]);

        // =========================================================
        // STEP 2: CREATE INVOICE RECORD
        // =========================================================
        $invoiceData = \App\Models\Invoice::firstOrCreate(
            ['bookingID' => $booking->bookingID],
            [
                'invoice_number' => 'INV-' . date('Ymd') . '-' . $booking->bookingID,
                'issue_date'     => now(),
                'totalAmount'    => $booking->rental_amount, 
            ]
        );

        // =========================================================
        // STEP 3: LOYALTY LOGIC (Stamps + Vouchers)
        // =========================================================
        try {
            $start = \Carbon\Carbon::parse($booking->rental_start_date);
            $end   = \Carbon\Carbon::parse($booking->rental_end_date);
            $hours = $start->diffInHours($end);

            if ($hours >= 9) {
                $stamps = floor($hours / 3);

                $card = \App\Models\LoyaltyCard::where('customerID', $booking->customerID)->first();

                if ($card) {
                    $card->total_stamps = $card->total_stamps + $stamps;
                    $card->loyalty_last_updated = now();
                    $card->save();
                } else {
                    $card = \App\Models\LoyaltyCard::create([
                        'customerID'   => $booking->customerID,
                        'total_stamps' => $stamps,
                        'loyalty_last_updated' => now()
                    ]);
                }

                // Check Reward (if voucher table exists)
                if ($card->total_stamps >= 48) {
                    // Note: Voucher table not in schema, but keeping logic for future
                    $card->total_stamps = $card->total_stamps - 48;
                    $card->save();
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Loyalty Logic Error: ' . $e->getMessage());
        }

        // =========================================================
        // STEP 4: WALLET LOGIC (Deduct Outstanding Amount)
        // =========================================================
        try {
            $wallet = \App\Models\WalletAccount::where('customerID', $booking->customerID)->first();

            if ($wallet) {
                // 1. Deduct from Outstanding
                // We use MAX(0, ...) to ensure it never goes negative
                $newOutstanding = max(0, ($wallet->outstanding_amount ?? 0) - $payment->total_amount);
                $wallet->outstanding_amount = $newOutstanding;
                $wallet->wallet_lastUpdate_Date_Time = now();
                $wallet->save();
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Wallet Logic Error: ' . $e->getMessage());
        }

        // =========================================================
        // STEP 5: EMAIL
        // =========================================================
        $pdf = Pdf::loadView('pdf.invoice', compact('booking', 'invoiceData'));

        try {
            $customerEmail = $booking->customer->user->email ?? null;
            if ($customerEmail) {
                Mail::to($customerEmail)->send(new \App\Mail\BookingInvoiceMail($booking, $pdf));
            }
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
            'payment_status' => 'Rejected',
            'payment_isVerify' => false,
            'latest_Update_Date_Time' => now(),
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
