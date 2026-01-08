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
        $search = $request->get('search');
        $filterPaymentDate = $request->get('filter_payment_date');
        $filterPaymentStatus = $request->get('filter_payment_status');
        $filterPaymentIsComplete = $request->get('filter_payment_is_complete');
        $filterPaymentIsVerify = $request->get('payment_isVerify');
        $filterVerifyBy = $request->get('filter_verify_by');
        
        $query = Payment::with(['booking.customer.user', 'booking.vehicle']);
        
        // Search by plate number
        if ($search) {
            $query->whereHas('booking.vehicle', function($vQuery) use ($search) {
                $vQuery->where('plate_number', 'like', "%{$search}%");
            });
        }
        
        // Filter by payment date
        if ($filterPaymentDate) {
            $query->whereDate('payment_date', $filterPaymentDate);
        }
        
        // Filter by payment status
        if ($filterPaymentStatus) {
            $query->where('payment_status', $filterPaymentStatus);
        }
        
        // Filter by payment isComplete
        if ($filterPaymentIsComplete !== null && $filterPaymentIsComplete !== '') {
            $query->where('isPayment_complete', (bool)$filterPaymentIsComplete);
        }
        
        // Filter by payment isVerify
        if ($filterPaymentIsVerify !== null && $filterPaymentIsVerify !== '') {
            $query->where('payment_isVerify', (bool)$filterPaymentIsVerify);
        }
        
        // Filter by verify_by
        if ($filterVerifyBy) {
            $query->where('verify_by', $filterVerifyBy);
        }
        
        // No sort function but usually display by desc payment time and date (default)
        $payments = $query->orderBy('payment_date', 'desc')
            ->orderBy('latest_Update_Date_Time', 'desc')
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
        
        // Get users who can verify (staff/admins) for verify_by filter
        $verifyByUsers = \App\Models\User::where(function($query) {
            $query->whereHas('staff')->orWhereHas('admin');
        })->orderBy('name')->get();

        return view('admin.payments.index', [
            'payments' => $payments,
            'search' => $search,
            'filterPaymentDate' => $filterPaymentDate,
            'filterPaymentStatus' => $filterPaymentStatus,
            'filterPaymentIsComplete' => $filterPaymentIsComplete,
            'filterPaymentIsVerify' => $filterPaymentIsVerify,
            'filterVerifyBy' => $filterVerifyBy,
            'verifyByUsers' => $verifyByUsers,
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
 /**
     * Approve a payment (Updated: 5 Stamps = 1 Voucher)
     */
    public function approve($id): \Illuminate\Http\RedirectResponse
    {
        $payment = \App\Models\Payment::with(['booking.vehicle', 'booking.customer.user'])->findOrFail($id);
        $booking = $payment->booking;

        // 1. Update Payment Status
        $payment->update([
            'payment_status'   => 'Verified',
            'payment_isVerify' => true,
            'latest_Update_Date_Time' => now(),
        ]);

        // 2. Update Booking Status
        if ($booking) {
            $booking->update(['booking_status' => 'Confirmed']);
        }

        // 3. Create Invoice
        $amountForInvoice = $booking->total_amount ?? $booking->rental_amount ?? 0;
        $invoiceData = \App\Models\Invoice::firstOrCreate(
            ['bookingID' => $booking->bookingID],
            [
                'invoice_number' => 'INV-' . date('Ymd') . '-' . $booking->bookingID,
                'issue_date'     => now(),
                'totalAmount'    => $amountForInvoice, 
            ]
        );

        // 4. LOYALTY LOGIC (Threshold: 5 Stamps)
        try {
            // Calculate stamps: 1 stamp per 3 hours
            $start = \Carbon\Carbon::parse($booking->rental_start_date ?? $booking->start_date);
            $end   = \Carbon\Carbon::parse($booking->rental_end_date ?? $booking->end_date);
            $hours = $start->diffInHours($end);

            // Minimum 9 hours to earn stamps? (You can remove this if you want stamps for shorter rides)
            if ($hours >= 9) {
                $stamps = floor($hours / 3);

                $card = \Illuminate\Support\Facades\DB::table('loyaltycard')
                    ->where('customerID', $booking->customerID)
                    ->first();

                if ($card) {
                    \Illuminate\Support\Facades\DB::table('loyaltycard')
                        ->where('loyaltyCardID', $card->loyaltyCardID)
                        ->update([
                            'total_stamps' => $card->total_stamps + $stamps,
                            'loyalty_last_updated' => now()
                        ]);
                    // Refresh card data
                    $card = \Illuminate\Support\Facades\DB::table('loyaltycard')->where('loyaltyCardID', $card->loyaltyCardID)->first();
                } else {
                    $newId = \Illuminate\Support\Facades\DB::table('loyaltycard')->insertGetId([
                        'customerID'   => $booking->customerID,
                        'total_stamps' => $stamps,
                        'loyalty_last_updated' => now()
                    ], 'loyaltyCardID');
                    $card = \Illuminate\Support\Facades\DB::table('loyaltycard')->where('loyaltyCardID', $newId)->first();
                }

                // === CHECK REWARD (Threshold: 5) ===
                if ($card->total_stamps >= 5) {
                    \Illuminate\Support\Facades\DB::table('voucher')->insert([
                        'loyaltyCardID' => $card->loyaltyCardID,
                        'discount_type' => '1 Free Day (Mon-Fri)',
                        'voucher_isActive' => 1,
                        'bookingID'     => $booking->bookingID,
                    ]);
                    
                    // Deduct 5 stamps
                    \Illuminate\Support\Facades\DB::table('loyaltycard')
                        ->where('loyaltyCardID', $card->loyaltyCardID)
                        ->decrement('total_stamps', 5);
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Loyalty Logic Error: ' . $e->getMessage());
        }

        // 5. Wallet Logic (Keep existing)
        try {
            $wallet = \Illuminate\Support\Facades\DB::table('walletaccount')->where('customerID', $booking->customerID)->first();
            if ($wallet) {
                $paymentAmount = $payment->total_amount ?? 0;
                $newOutstanding = max(0, $wallet->outstanding_amount - $paymentAmount);
                \Illuminate\Support\Facades\DB::table('walletaccount')
                    ->where('walletAccountID', $wallet->walletAccountID)
                    ->update(['outstanding_amount' => $newOutstanding, 'wallet_lastUpdate_Date_Time' => now()]);
            }
        } catch (\Exception $e) {}

        // 6. Email Logic (Keep existing)
        $recipientEmail = $booking->customer->user->email ?? null;
        if ($recipientEmail) {
            try {
                $rentalAmount = $booking->rental_amount;
                $depositAmount = $booking->deposit_amount;
                $totalPaid = $booking->payments()->where('payment_status', 'Verified')->sum('total_amount');
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice', compact('booking', 'invoiceData', 'rentalAmount', 'depositAmount', 'totalPaid'));
                \Illuminate\Support\Facades\Mail::to($recipientEmail)->send(new \App\Mail\BookingInvoiceMail($booking, $pdf));
            } catch (\Exception $e) {}
        }

        return redirect()->route('admin.payments.index')
            ->with('success', 'Payment Verified. Loyalty Updated (5 Stamps = Voucher).');
    }

    /**
     * Update Verify Toggle (Updated: 5 Stamps = 1 Voucher)
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
            
            \App\Models\Invoice::firstOrCreate(
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

            // === LOYALTY LOGIC (Threshold: 5 Stamps) ===
            try {
                $start = \Carbon\Carbon::parse($booking->rental_start_date ?? $booking->start_date);
                $end   = \Carbon\Carbon::parse($booking->rental_end_date ?? $booking->end_date);
                $hours = $start->diffInHours($end);

                if ($hours >= 9) {
                    $stamps = floor($hours / 3);
                    $card = \Illuminate\Support\Facades\DB::table('loyaltycard')->where('customerID', $booking->customerID)->first();

                    if ($card) {
                        \Illuminate\Support\Facades\DB::table('loyaltycard')->where('loyaltyCardID', $card->loyaltyCardID)
                            ->update(['total_stamps' => $card->total_stamps + $stamps, 'loyalty_last_updated' => now()]);
                        $card = \Illuminate\Support\Facades\DB::table('loyaltycard')->where('loyaltyCardID', $card->loyaltyCardID)->first();
                    } else {
                        $newId = \Illuminate\Support\Facades\DB::table('loyaltycard')->insertGetId([
                            'customerID' => $booking->customerID, 'total_stamps' => $stamps, 'loyalty_last_updated' => now()
                        ], 'loyaltyCardID');
                        $card = \Illuminate\Support\Facades\DB::table('loyaltycard')->where('loyaltyCardID', $newId)->first();
                    }

                    // CHECK REWARD (5)
                    if ($card->total_stamps >= 5) {
                        \Illuminate\Support\Facades\DB::table('voucher')->insert([
                            'loyaltyCardID' => $card->loyaltyCardID,
                            'discount_type' => '1 Free Day (Mon-Fri)',
                            'voucher_isActive' => 1,
                            'bookingID' => $booking->bookingID,
                        ]);
                        \Illuminate\Support\Facades\DB::table('loyaltycard')->where('loyaltyCardID', $card->loyaltyCardID)
                            ->decrement('total_stamps', 5);
                    }
                }
            } catch (\Exception $e) { }

            // Wallet & Email Logic (Same as above)
            // ... (keep wallet logic) ...
            // ... (keep email logic) ...

        } else {
            $updateData['payment_status'] = 'Pending';
            $updateData['isPayment_complete'] = false;
        }
        
        $payment->update($updateData);
        
        return redirect()->route('admin.payments.index')->with('success', 'Payment verification updated.');
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
        
        // Create invoice record if it doesn't exist
        $invoiceData = Invoice::firstOrCreate(
            ['bookingID' => $booking->bookingID],
            [
                'invoice_number' => 'INV-' . date('Ymd') . '-' . $booking->bookingID,
                'issue_date' => now(),
                'totalAmount' => ($booking->rental_amount ?? 0) + ($booking->deposit_amount ?? 0),
            ]
        );

        $pdf = Pdf::loadView('pdf.invoice', compact('booking', 'invoiceData', 'rentalAmount', 'depositAmount', 'totalPaid'));
        return $pdf->download('Invoice-'.$booking->bookingID.'.pdf');
    }
}