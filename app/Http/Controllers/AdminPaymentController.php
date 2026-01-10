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
use Illuminate\Support\Facades\Log;

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
        
        // Search by plate number or booking ID
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('bookingID', 'like', "%{$search}%")
                  ->orWhereHas('booking.vehicle', function($vQuery) use ($search) {
                      $vQuery->where('plate_number', 'like', "%{$search}%");
                  });
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
     * Approve a payment
     * - Verifies Payment & Booking
     * - Generates Invoice
     * - Updates Loyalty Points (5 Bookings = 1 Voucher)
     * - Deducts Wallet Outstanding Balance
     * - Sends Invoice via Email
     */
    public function approve($id): \Illuminate\Http\RedirectResponse
    {
        // =========================================================
        // STEP 1: GET DATA
        // =========================================================
        $payment = \App\Models\Payment::with(['booking.vehicle', 'booking.customer.user'])->findOrFail($id);
        $booking = $payment->booking;

        // =========================================================
        // STEP 2: VERIFY *THIS* SPECIFIC PAYMENT ROW
        // =========================================================
        // We mark this specific receipt (e.g. Deposit or Balance) as valid.
        $payment->update([
            'payment_status'          => 'Verified',
            'payment_isVerify'        => 1, // Unlock this amount for calculation
            'latest_Update_Date_Time' => now(),
        ]);

        // =========================================================
        // STEP 3: CHECK OVERALL BOOKING STATUS (The "2-Row" Logic)
        // =========================================================
        if ($booking) {
            // A. Calculate Total Verified Amount (Sum of Row 1 + Row 2 + ...)
            $totalVerifiedPaid = $booking->payments()
                ->where('payment_status', 'Verified')
                ->sum('total_amount');

            // B. Get Grand Total Price
            $grandTotal = $booking->rental_amount; // Assuming this is the final price

            // C. Compare & Update Status
            // Use ($grandTotal - 1) to handle small rounding differences
            if ($totalVerifiedPaid >= ($grandTotal - 1.00)) {
                // Scenario: Fully Paid (Deposit + Balance)
                $booking->update([
                    'booking_status' => 'Confirmed' // User sees "Ready for Pickup" / "Fully Verified"
                ]);

                // Mark payments as complete cycle (Optional, based on your DB column)
                $booking->payments()->update(['isPayment_complete' => 1]); 

            } else {
                // Scenario: Partial Payment (Deposit Only)
                $booking->update([
                    'booking_status' => 'Reserved' // User sees "Deposit Verified"
                ]);
            }
        }

        // =========================================================
        // STEP 4: GENERATE INVOICE
        // =========================================================
        // We update the invoice every time a payment is verified so it reflects the latest status
        $amountForInvoice = $booking->rental_amount ?? 0;
        $invoiceData = \App\Models\Invoice::firstOrCreate(
            ['bookingID' => $booking->bookingID],
            [
                'invoice_number' => 'INV-' . date('Ymd') . '-' . $booking->bookingID,
                'issue_date'     => now(),
                'totalAmount'    => $amountForInvoice, 
            ]
        );

        // =========================================================
        // STEP 5: LOYALTY LOGIC (Give Stamps)
        // =========================================================
        // Note: You might want to wrap this in "if fully paid" to prevent spamming stamps
        // But here is your original logic preserved:
        try {
            $stamps = 1;
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
                $card = \Illuminate\Support\Facades\DB::table('loyaltycard')->where('loyaltyCardID', $card->loyaltyCardID)->first();
            } else {
                $newId = \Illuminate\Support\Facades\DB::table('loyaltycard')->insertGetId([
                    'customerID'   => $booking->customerID,
                    'total_stamps' => $stamps,
                    'loyalty_last_updated' => now()
                ], 'loyaltyCardID');
                $card = \Illuminate\Support\Facades\DB::table('loyaltycard')->where('loyaltyCardID', $newId)->first();
            }

            // Auto-Issue Voucher if > 5 stamps
            while ($card->total_stamps >= 5) {
                \Illuminate\Support\Facades\DB::table('voucher')->insert([
                    'loyaltyCardID' => $card->loyaltyCardID,
                    'discount_type' => 'Loyalty Reward (Free Booking)',
                    'voucher_isActive' => 1,
                    'bookingID'     => $booking->bookingID,
                    'voucher_code'  => 'LOYALTY-' . strtoupper(\Illuminate\Support\Str::random(6)),
                    'created_at'    => now()
                ]);
                
                \Illuminate\Support\Facades\DB::table('loyaltycard')
                    ->where('loyaltyCardID', $card->loyaltyCardID)
                    ->decrement('total_stamps', 5);
                
                $card = \Illuminate\Support\Facades\DB::table('loyaltycard')->where('loyaltyCardID', $card->loyaltyCardID)->first();
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Loyalty Logic Error: ' . $e->getMessage());
        }

        // =========================================================
        // STEP 6: WALLET LOGIC (Deduct "Outstanding" Debt)
        // =========================================================
        try {
            $wallet = \Illuminate\Support\Facades\DB::table('walletaccount')
                ->where('customerID', $booking->customerID)
                ->first();

            if ($wallet) {
                // We reduce the debt by the amount JUST paid in this specific receipt
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

        // =========================================================
        // STEP 7: SEND EMAIL
        // =========================================================
        $recipientEmail = $booking->customer->user->email ?? null;
        if ($recipientEmail) {
            try {
                // Prepare Invoice Data
                $rentalAmount = $booking->rental_amount;
                $depositAmount = $booking->deposit_amount;
                // Re-calculate total paid for the PDF
                $totalPaid = $booking->payments()->where('payment_status', 'Verified')->sum('total_amount');
                
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice', compact('booking', 'invoiceData', 'rentalAmount', 'depositAmount', 'totalPaid'));
                
                \Illuminate\Support\Facades\Mail::to($recipientEmail)
                    ->send(new \App\Mail\BookingInvoiceMail($booking, $pdf));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Mail Error: ' . $e->getMessage());
            }
        }

        return redirect()->route('admin.payments.index')
            ->with('success', 'Payment Verified. Booking status updated based on total payment.');
    }
   

    /**
     * Update Verify Toggle (Updated: 5 Bookings = 1 Voucher)
     */
    public function updateVerify(Request $request, $id): \Illuminate\Http\RedirectResponse
    {
        $payment = \App\Models\Payment::with(['booking.customer.user'])->findOrFail($id);
        $booking = $payment->booking;
        
        $isVerify = $request->input('payment_isVerify') == '1' || $request->input('payment_isVerify') === true;
        $verifyBy = $request->input('verify_by');
        
        $updateData = [
            'payment_isVerify' => $isVerify,
            'latest_Update_Date_Time' => \Carbon\Carbon::now(),
        ];
        
        // if ($isVerify && $verifyBy) {
        //     $updateData['verify_by'] = $verifyBy;
        // } elseif ($isVerify && Auth::check()) {
        //     $updateData['verify_by'] = Auth::user()->userID;
        // }
        
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

            // =========================================================
            // LOYALTY LOGIC (5 Bookings = 1 Voucher)
            // =========================================================
            try {
                $stamps = 1;
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

                while ($card->total_stamps >= 5) {
                    \Illuminate\Support\Facades\DB::table('voucher')->insert([
                        'loyaltyCardID' => $card->loyaltyCardID,
                        'discount_type' => 'Loyalty Reward (Free Booking)',
                        'voucher_isActive' => 1,
                        'bookingID' => $booking->bookingID,
                        'voucher_code'  => 'LOYALTY-' . strtoupper(Str::random(6)),
                        'created_at'    => now()
                    ]);
                    \Illuminate\Support\Facades\DB::table('loyaltycard')->where('loyaltyCardID', $card->loyaltyCardID)
                        ->decrement('total_stamps', 5);
                    
                    $card = \Illuminate\Support\Facades\DB::table('loyaltycard')->where('loyaltyCardID', $card->loyaltyCardID)->first();
                }
            } catch (\Exception $e) { 
                Log::warning('Loyalty Error: ' . $e->getMessage());
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
                Log::warning('Wallet Update Failed: ' . $e->getMessage());
            }

            // Email Logic
            $recipientEmail = $booking->customer->user->email ?? null;
            if ($recipientEmail) {
                try {
                    $rentalAmount = $booking->rental_amount;
                    $depositAmount = $booking->deposit_amount;
                    $totalPaid = $booking->payments()->where('payment_status', 'Verified')->sum('total_amount');
                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice', compact('booking', 'invoiceData', 'rentalAmount', 'depositAmount', 'totalPaid'));
                    \Illuminate\Support\Facades\Mail::to($recipientEmail)->send(new \App\Mail\BookingInvoiceMail($booking, $pdf));
                } catch (\Exception $e) {
                    Log::error('Toggle Mail Error: ' . $e->getMessage());
                }
            }

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

    public function generateInvoice($id)
    {
        $payment = Payment::where('paymentID', $id)->firstOrFail();
        $booking = $payment->booking;
        $rentalAmount = $booking->rental_amount ?? 0;
        $depositAmount = $booking->deposit_amount ?? 0;
        $totalPaid = $booking->payments()->where('payment_status', 'Verified')->sum('total_amount');
        
        $existingInvoice = Invoice::where('bookingID', $booking->bookingID)->first();
        
        if ($existingInvoice) {
            $existingInvoice->update([
                'issue_date' => now(),
                'totalAmount' => $rentalAmount + $depositAmount,
            ]);
            $invoiceData = $existingInvoice->fresh();
        } else {
            $invoiceData = Invoice::create([
                'bookingID' => $booking->bookingID,
                'invoice_number' => 'INV-' . date('Ymd') . '-' . $booking->bookingID,
                'issue_date' => now(),
                'totalAmount' => $rentalAmount + $depositAmount,
            ]);
        }
        
        $booking->load('invoice');

        $pdf = Pdf::loadView('pdf.invoice', compact('booking', 'invoiceData', 'rentalAmount', 'depositAmount', 'totalPaid'));
        return $pdf->download('Invoice-'.$booking->bookingID.'.pdf');
    }
}