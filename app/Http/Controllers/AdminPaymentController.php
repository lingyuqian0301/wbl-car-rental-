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
        
        if ($filterBookingStatus) {
            $query->whereHas('booking', function($q) use ($filterBookingStatus) {
                $q->where('booking_status', $filterBookingStatus);
            });
        }
        
        $payments = $query->orderBy('payment_date', 'desc')
            ->orderBy('paymentID', 'desc')
            ->paginate(20)->withQueryString();

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
        $payment = \App\Models\Payment::with(['booking.customer', 'booking.vehicle'])->findOrFail($id);
        return view('admin.payments.show', compact('payment'));
    }

    /**
     * Approve a payment, generate an invoice record, and send the Gmail.
     */
    public function approve($id): \Illuminate\Http\RedirectResponse
    {
        $payment = \App\Models\Payment::with(['booking.vehicle', 'booking.customer.user'])->findOrFail($id);
        $booking = $payment->booking;

        $payment->update([
            'payment_status'   => 'Verified',
            'payment_isVerify' => true,
            'latest_Update_Date_Time' => now(),
        ]);

        if ($booking) {
            $booking->update(['booking_status' => 'Confirmed']);
        }

        $amountForInvoice = $booking->total_amount ?? $booking->rental_amount ?? 0;
        $invoiceData = \App\Models\Invoice::firstOrCreate(
            ['bookingID' => $booking->bookingID],
            [
                'invoice_number' => 'INV-' . date('Ymd') . '-' . $booking->bookingID,
                'issue_date'     => now(),
                'totalAmount'    => $amountForInvoice, 
            ]
        );

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
            \Illuminate\Support\Facades\Log::warning('Wallet Logic Error: ' . $e->getMessage());
        }

        // --- GMAIL SENDING LOGIC ---
        $recipientEmail = $booking->customer->user->email ?? null;
        if ($recipientEmail) {
            try {
                $rentalAmount = $booking->rental_amount;
                $depositAmount = $booking->deposit_amount;
                $totalPaid = $booking->payments()->where('payment_status', 'Verified')->sum('total_amount');
                
                // Load PDF
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice', compact('booking', 'invoiceData', 'rentalAmount', 'depositAmount', 'totalPaid'));
                
                // Send Mail
                \Illuminate\Support\Facades\Mail::to($recipientEmail)
                    ->send(new \App\Mail\BookingInvoiceMail($booking, $pdf));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Mail Error: ' . $e->getMessage());
            }
        }

        return redirect()->route('admin.payments.index')
            ->with('success', 'Payment Verified. Wallet Balance Updated and Invoice Sent.');
    }

    public function reject($id): RedirectResponse
    {
        $payment = Payment::where('paymentID', $id)->firstOrFail();
        $payment->update([
            'payment_status' => 'Rejected',
            'latest_Update_Date_Time' => now(),
        ]);

        return redirect()->route('admin.payments.index')->with('success', 'Payment rejected.');
    }

    /**
     * Update payment verification status (Toggle Switch).
     */
    public function updateVerify(Request $request, $id): \Illuminate\Http\RedirectResponse
    {
        $payment = \App\Models\Payment::with(['booking.customer.user'])->findOrFail($id);
        $booking = $payment->booking;
        
        $isVerify = $request->input('payment_isVerify') == '1' || $request->input('payment_isVerify') === true;
        
        $updateData = [
            'payment_isVerify' => $isVerify,
            'latest_Update_Date_Time' => \Carbon\Carbon::now(),
        ];
        
        if ($isVerify) {
            $updateData['payment_status'] = 'Verified';
            
            $invoiceData = \App\Models\Invoice::firstOrCreate(
                ['bookingID' => $booking->bookingID],
                [
                    'invoice_number' => 'INV-' . date('Ymd') . '-' . $booking->bookingID,
                    'issue_date'     => now(),
                    'totalAmount'    => $booking->total_amount ?? $booking->rental_amount,
                ]
            );

            if ($booking) {
                $booking->update(['booking_status' => 'Confirmed']);
            }

            // Wallet Logic
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

            // --- GMAIL SENDING LOGIC FOR TOGGLE ---
            $recipientEmail = $booking->customer->user->email ?? null;
            if ($recipientEmail) {
                try {
                    $rentalAmount = $booking->rental_amount;
                    $depositAmount = $booking->deposit_amount;
                    $totalPaid = $booking->payments()->where('payment_status', 'Verified')->sum('total_amount');
                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice', compact('booking', 'invoiceData', 'rentalAmount', 'depositAmount', 'totalPaid'));
                    \Illuminate\Support\Facades\Mail::to($recipientEmail)->send(new \App\Mail\BookingInvoiceMail($booking, $pdf));
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Toggle Mail Error: ' . $e->getMessage());
                }
            }

        } else {
            $updateData['payment_status'] = 'Pending';
            $updateData['isPayment_complete'] = false;
        }
        
        $payment->update($updateData);
        
        return redirect()->route('admin.payments.index')->with('success', 'Payment verification updated.');
    }

    public function generateInvoice($id)
    {
        $payment = Payment::where('paymentID', $id)->firstOrFail();
        $booking = $payment->booking;
        $invoiceData = Invoice::where('bookingID', $booking->bookingID)->first();
        $rentalAmount = $booking->rental_amount;
        $depositAmount = $booking->deposit_amount;
        $totalPaid = $booking->payments->where('payment_status', 'Verified')->sum('total_amount');

        $pdf = Pdf::loadView('pdf.invoice', compact('booking', 'invoiceData', 'rentalAmount', 'depositAmount', 'totalPaid'));
        return $pdf->download('Invoice-'.$booking->bookingID.'.pdf');
    }
}