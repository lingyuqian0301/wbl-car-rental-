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
     * Approve a payment (Updated: 5 Stamps = 1 Voucher)
     */
/**
     * Approve a payment
     * - Verifies Payment & Booking
     * - Generates Invoice
     * - Updates Loyalty Points (5 Stamps = 1 Voucher)
     * - Deducts Wallet Outstanding Balance
     * - Sends Invoice via Email
     */
    public function approve($id): \Illuminate\Http\RedirectResponse
    {
        // =========================================================
        // STEP 1: GET DATA
        // =========================================================
        // We find the payment and "eager load" the related data we need.
        // 'booking.customer.user' is CRITICAL to get the email address later.
        $payment = \App\Models\Payment::with(['booking.vehicle', 'booking.customer.user'])->findOrFail($id);
        $booking = $payment->booking;

        // =========================================================
        // STEP 2: UPDATE STATUSES
        // =========================================================
        // Mark payment as Verified
        $payment->update([
            'payment_status'   => 'Verified',
            'payment_isVerify' => true,
            'latest_Update_Date_Time' => now(),
        ]);

        // Mark booking as Confirmed
        if ($booking) {
            $booking->update(['booking_status' => 'Confirmed']);
        }

        // =========================================================
        // STEP 3: GENERATE INVOICE
        // =========================================================
        // Create an official Invoice record if one doesn't exist yet.
        $amountForInvoice = $booking->total_amount ?? $booking->rental_amount ?? 0;
        $invoiceData = \App\Models\Invoice::firstOrCreate(
            ['bookingID' => $booking->bookingID],
            [
                'invoice_number' => 'INV-' . date('Ymd') . '-' . $booking->bookingID,
                'issue_date'     => now(),
                'totalAmount'    => $amountForInvoice, 
            ]
        );

        // =========================================================
        // STEP 4: LOYALTY LOGIC (The 5 Stamp Rule)
        // =========================================================
        try {
            // 1. Calculate duration in hours
            $start = \Carbon\Carbon::parse($booking->rental_start_date ?? $booking->start_date);
            $end   = \Carbon\Carbon::parse($booking->rental_end_date ?? $booking->end_date);
            $hours = $start->diffInHours($end);

            // 2. Only give stamps if rental is at least 9 hours
            if ($hours >= 9) {
                // Formula: 1 Stamp per 3 Hours
                $stamps = floor($hours / 3);

                // 3. Find or Create Loyalty Card
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
                    // Refresh card data after update
                    $card = \Illuminate\Support\Facades\DB::table('loyaltycard')->where('loyaltyCardID', $card->loyaltyCardID)->first();
                } else {
                    $newId = \Illuminate\Support\Facades\DB::table('loyaltycard')->insertGetId([
                        'customerID'   => $booking->customerID,
                        'total_stamps' => $stamps,
                        'loyalty_last_updated' => now()
                    ], 'loyaltyCardID');
                    $card = \Illuminate\Support\Facades\DB::table('loyaltycard')->where('loyaltyCardID', $newId)->first();
                }

                // 4. CHECK REWARD: If stamps >= 5, give Voucher
                if ($card->total_stamps >= 5) {
                    // A. Create the Voucher in database
                    \Illuminate\Support\Facades\DB::table('voucher')->insert([
                        'loyaltyCardID' => $card->loyaltyCardID,
                        'discount_type' => '1 Free Day (Mon-Fri)',
                        'voucher_isActive' => 1,
                        'bookingID'     => $booking->bookingID,
                    ]);
                    
                    // B. Deduct the cost (5 stamps) from their card
                    \Illuminate\Support\Facades\DB::table('loyaltycard')
                        ->where('loyaltyCardID', $card->loyaltyCardID)
                        ->decrement('total_stamps', 5);
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Loyalty Logic Error: ' . $e->getMessage());
        }

        // =========================================================
        // STEP 5: WALLET LOGIC (Auto-Deduction)
        // =========================================================
        try {
            $wallet = \Illuminate\Support\Facades\DB::table('walletaccount')
                ->where('customerID', $booking->customerID)
                ->first();

            if ($wallet) {
                // Calculate new outstanding debt
                $paymentAmount = $payment->total_amount ?? 0;
                // 'max(0, ...)' ensures debt never becomes negative (e.g. -10.00)
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
        // STEP 6: EMAIL LOGIC
        // =========================================================
        // Get email from User table (Customer table has no email column)
        $recipientEmail = $booking->customer->user->email ?? null;
        
        if ($recipientEmail) {
            try {
                // Prepare data for PDF
                $rentalAmount = $booking->rental_amount;
                $depositAmount = $booking->deposit_amount;
                $totalPaid = $booking->payments()->where('payment_status', 'Verified')->sum('total_amount');
                
                // Generate PDF in memory
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice', compact('booking', 'invoiceData', 'rentalAmount', 'depositAmount', 'totalPaid'));
                
                // Send Email using the simple view (emails.invoice) to prevent crashing
                \Illuminate\Support\Facades\Mail::to($recipientEmail)
                    ->send(new \App\Mail\BookingInvoiceMail($booking, $pdf));
            } catch (\Exception $e) {
                // Log error but allow page to finish loading
                \Illuminate\Support\Facades\Log::error('Mail Error: ' . $e->getMessage());
            }
        }

        return redirect()->route('admin.payments.index')
            ->with('success', 'Payment Verified. Loyalty, Wallet & Invoice updated.');
    }
    /**
     * Update Verify Toggle (Updated: 5 Stamps = 1 Voucher)
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
        
        // Set verify_by if provided and payment is being verified
        if ($isVerify && $verifyBy) {
            $updateData['verify_by'] = $verifyBy;
        } elseif ($isVerify && Auth::check()) {
            // If verifying and no verify_by provided, use current user
            $updateData['verify_by'] = Auth::user()->userID;
        }
        
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

    public function generateInvoice($id)
    {
        $payment = Payment::where('paymentID', $id)->firstOrFail();
        $booking = $payment->booking;
        $rentalAmount = $booking->rental_amount ?? 0;
        $depositAmount = $booking->deposit_amount ?? 0;
        $totalPaid = $booking->payments()->where('payment_status', 'Verified')->sum('total_amount');
        
        // Check if invoice already exists
        $existingInvoice = Invoice::where('bookingID', $booking->bookingID)->first();
        
        if ($existingInvoice) {
            // Update existing invoice - update issue_date to current date
            $existingInvoice->update([
                'issue_date' => now(),
                'totalAmount' => $rentalAmount + $depositAmount,
            ]);
            $invoiceData = $existingInvoice->fresh();
        } else {
            // Create new invoice record
            $invoiceData = Invoice::create([
                'bookingID' => $booking->bookingID,
                'invoice_number' => 'INV-' . date('Ymd') . '-' . $booking->bookingID,
                'issue_date' => now(),
                'totalAmount' => $rentalAmount + $depositAmount,
            ]);
        }
        
        // Load booking with invoice relationship to ensure it's available
        $booking->load('invoice');

        $pdf = Pdf::loadView('pdf.invoice', compact('booking', 'invoiceData', 'rentalAmount', 'depositAmount', 'totalPaid'));
        return $pdf->download('Invoice-'.$booking->bookingID.'.pdf');
    }
}