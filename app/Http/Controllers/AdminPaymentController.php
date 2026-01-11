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

        // Get all active staffit and admins for verified by dropdown (exclude runner)
        $staffUsers = \App\Models\User::where(function($query) {
            $query->whereHas('staff', function($q) {
                $q->where('isActive', true)
                  ->whereDoesntHave('runner'); // Exclude runners
            })->orWhereHas('admin', function($q) {
                $q->where('isActive', true);
            });
        })->where('isActive', true)->with(['staff.runner', 'staff.staffIt', 'admin'])->orderBy('name')->get();

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
            'totalPayments' => $totalPayments,
            'totalPending' => $totalPending,
            'totalVerified' => $totalVerified,
            'totalFullPayment' => $totalFullPayment,
            'totalToday' => $totalToday,
            'today' => $today,
            'staffUsers' => $staffUsers,
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
            $booking->update(['booking_status' => 'Confirmed']);
            
            // Check if full payment is made - if so, mark upcoming booking notifications as read
            $totalRequired = ($booking->rental_amount ?? 0) + ($booking->deposit_amount ?? 0);
            $totalPaid = $booking->payments()
                ->where('payment_status', 'Verified')
                ->sum('total_amount');
            
            if ($totalPaid >= $totalRequired) {
                // Mark upcoming booking payment incomplete notifications as read
                try {
                    \App\Models\AdminNotification::where('booking_id', $booking->bookingID)
                        ->where('type', 'upcoming_booking_payment_incomplete')
                        ->where('is_read', false)
                        ->update([
                            'is_read' => true,
                            'read_at' => now(),
                        ]);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning('Failed to mark upcoming booking notifications as read: ' . $e->getMessage());
                }

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

            // Auto-Issue Voucher if >= 5 stamps (10% discount)
            while ($card->total_stamps >= 5) {
                \Illuminate\Support\Facades\DB::table('voucher')->insert([
                    'loyaltyCardID' => $card->loyaltyCardID,
                    'discount_type' => 'PERCENT',
                    'discount_amount' => 10, // 10% discount
                    'voucher_isActive' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
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
}


    /**
     * Update Verify Toggle (Updated: 5 Bookings = 1 Voucher)
     */
    public function updateVerify(Request $request, $id)
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

            // FIRST: Update the payment immediately so it's included in subsequent queries
            $payment->update($updateData);

            $invoiceData = \App\Models\Invoice::firstOrCreate(
                ['bookingID' => $booking->bookingID],
                [
                    'invoice_number' => 'INV-' . date('Ymd') . '-' . $booking->bookingID,
                    'issue_date'     => now(),
                    'totalAmount'    => $booking->rental_amount ?? 0,
                ]
            );

            // Update booking status based on total verified payments
            // NOTE: If customer pays deposit and balance separately, admin needs to verify twice:
            // 1. First verification (deposit) → Status: 'Reserved' (partial payment)
            // 2. Second verification (balance) → Status: 'Confirmed' (fully paid)
            // If customer pays full amount in one payment, admin only verifies once → Status: 'Confirmed'
            if ($booking) {
                // Check if full payment is made - if so, mark upcoming booking notifications as read
                $totalRequired = ($booking->rental_amount ?? 0) + ($booking->deposit_amount ?? 0);
                $totalPaid = $booking->payments()
                    ->where('payment_status', 'Verified')
                    ->sum('total_amount');
                
                if ($totalPaid >= $totalRequired) {
                    // Mark upcoming booking payment incomplete notifications as read
                    try {
                        \App\Models\AdminNotification::where('booking_id', $booking->bookingID)
                            ->where('type', 'upcoming_booking_payment_incomplete')
                            ->where('is_read', false)
                            ->update([
                                'is_read' => true,
                                'read_at' => now(),
                            ]);
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::warning('Failed to mark upcoming booking notifications as read: ' . $e->getMessage());
                    }
                }

                // Calculate total verified amount (now includes the current payment)
                $totalVerifiedPaid = $booking->payments()
                    ->where('payment_status', 'Verified')
                    ->sum('total_amount');

                // Get grand total (rental_amount already includes everything)
                $grandTotal = $booking->rental_amount ?? 0;

                // Update status based on payment completion
                if ($totalVerifiedPaid >= ($grandTotal - 1.00)) {
                    // Fully Paid (deposit + balance both verified) - Set to Confirmed
                    $booking->update([
                        'booking_status' => 'Confirmed',
                        'lastUpdateDate' => now()
                    ]);
                    // Mark payments as complete
                    $booking->payments()->where('payment_status', 'Verified')->update(['isPayment_complete' => 1]);
                } elseif ($totalVerifiedPaid > 0) {
                    // Partial Payment (only deposit verified, balance pending) - Set to Reserved
                    $booking->update([
                        'booking_status' => 'Reserved',
                        'lastUpdateDate' => now()
                    ]);
                } else {
                    // No verified payments - Keep as Pending
                    $booking->update([
                        'booking_status' => 'Pending',
                        'lastUpdateDate' => now()
                    ]);
                }
            }

            // =========================================================
            // LOYALTY LOGIC (5 Bookings = 1 Voucher)
            // =========================================================
                // =========================================================
        // STEP 5: LOYALTY LOGIC (Corrected for your Table)
        // =========================================================
        try {
            // 1. Give 1 Stamp
            $stamps = 0;

            // 2. Find/Create Card
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
                // Refresh data
                $card = \Illuminate\Support\Facades\DB::table('loyaltycard')->where('loyaltyCardID', $card->loyaltyCardID)->first();
            } else {
                $newId = \Illuminate\Support\Facades\DB::table('loyaltycard')->insertGetId([
                    'customerID'   => $booking->customerID,
                    'total_stamps' => $stamps,
                    'loyalty_last_updated' => now()
                ], 'loyaltyCardID'); // Assumes primary key is loyaltyCardID
                $card = \Illuminate\Support\Facades\DB::table('loyaltycard')->where('loyaltyCardID', $newId)->first();
            }

            // 3. CHECK REWARD (If >= 5 stamps, convert to Voucher - 10% discount)
            while ($card->total_stamps >= 5) {

                // A. Insert into 'voucher' using YOUR table structure
                \Illuminate\Support\Facades\DB::table('voucher')->insert([
                    'loyaltyCardID'    => $card->loyaltyCardID, // Links to the user
                    'discount_type'    => 'PERCENT',            // Use 'PERCENT' for 10% discount
                    'discount_amount'  => 10,                   // 10% discount
                    'voucher_isActive' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // B. Deduct 5 stamps
                \Illuminate\Support\Facades\DB::table('loyaltycard')
                    ->where('loyaltyCardID', $card->loyaltyCardID)
                    ->decrement('total_stamps', 5);

                // Refresh for loop
                $card = \Illuminate\Support\Facades\DB::table('loyaltycard')->where('loyaltyCardID', $card->loyaltyCardID)->first();
            }

        } catch (\Exception $e) {
            // If this fails, it will now log the specific error to your laravel.log file
            \Illuminate\Support\Facades\Log::error('Loyalty Error: ' . $e->getMessage());
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

            // Update payment first
            $payment->update($updateData);

            // If unverifying, also update booking status based on remaining verified payments
            if ($booking) {
                $totalVerifiedPaid = $booking->payments()
                    ->where('payment_status', 'Verified')
                    ->sum('total_amount');

                $grandTotal = $booking->rental_amount ?? 0;

                if ($totalVerifiedPaid >= ($grandTotal - 1.00)) {
                    $booking->update([
                        'booking_status' => 'Confirmed',
                        'lastUpdateDate' => now()
                    ]);
                } elseif ($totalVerifiedPaid > 0) {
                    $booking->update([
                        'booking_status' => 'Reserved',
                        'lastUpdateDate' => now()
                    ]);
                } else {
                    $booking->update([
                        'booking_status' => 'Pending',
                        'lastUpdateDate' => now()
                    ]);
                }
            }
        }

        // Return JSON if requested, otherwise redirect
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment verification updated. Booking status synchronized.'
            ]);
        }

        return redirect()->route('admin.payments.index')->with('success', 'Payment verification updated. Booking status synchronized.');
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
        // 1. FETCH MAIN DATA
        $payment = \App\Models\Payment::where('paymentID', $id)->firstOrFail();
        $booking = $payment->booking; // Assumes relation exists
        
        // Load relationships to avoid N+1 queries
        $booking->load(['customer.user', 'vehicle', 'payments']);
        
        $customer = $booking->customer;
        $user = $customer->user;
        $vehicle = $booking->vehicle; // <--- Fixes "Undefined variable $vehicle"

        // 2. FETCH CUSTOMER IDENTITY DETAILS
        $localCustomer = \App\Models\Local::where('customerID', $customer->customerID)->first();
        $localstudent  = \App\Models\LocalStudent::where('customerID', $customer->customerID)->first();
        $internationalCustomer = \App\Models\International::where('customerID', $customer->customerID)->first();

        // 3. CALCULATE INVOICE ITEMS
        // A. Vehicle Rental
        $dailyRate = $vehicle->rental_price;
        $duration = $booking->duration ?? 1;
        $rentalBase = $dailyRate * $duration;

        // B. Add-ons Breakdown
        $addonsString = $booking->addOns_item; // e.g. "power_bank,usb_wire"
        $addonsArray = $addonsString ? explode(',', $addonsString) : [];
        $addonPrices = [
            'power_bank' => 5,
            'phone_holder' => 5,
            'usb_wire' => 3,
        ];
        $addonNames = [
            'power_bank' => 'Power Bank',
            'phone_holder' => 'Phone Holder',
            'usb_wire' => 'USB Wire',
        ];
        
        $addonsBreakdown = [];
        $addonsTotal = 0;
        
        foreach ($addonsArray as $item) {
            $key = trim($item);
            if (isset($addonPrices[$key])) {
                $price = $addonPrices[$key];
                $total = $price * $duration;
                $addonsTotal += $total;
                $addonsBreakdown[] = [
                    'name' => $addonNames[$key] ?? ucwords(str_replace('_', ' ', $key)),
                    'duration' => $duration,
                    'daily_price' => $price,
                    'total' => $total
                ];
            }
        }

        // C. Pickup Surcharge (Logic: RM10 if not HQ)
        $pickupSurcharge = ($booking->pickup_point === 'HASTA HQ Office') ? 0 : 10;

        // D. Deposit
        $depositAmount = $booking->deposit_amount ?? 50; // Default 50 if null

        // 4. CALCULATE TOTALS & DISCOUNT
        $baseAmount = $rentalBase + $addonsTotal + $pickupSurcharge; // Subtotal before discount
        $calculatedTotalWithDeposit = $baseAmount + $depositAmount;
        
        // The 'rental_amount' in DB is the FINAL amount the user agreed to pay.
        // If Calculated > DB Amount, the difference is the Discount.
        $finalTotal = $booking->rental_amount; 
        $discountAmount = max(0, $calculatedTotalWithDeposit - $finalTotal);
        
        $subtotalAfterDiscount = $baseAmount - $discountAmount;

        // Mock Voucher Object for View (if discount exists)
        $voucher = null;
        if ($discountAmount > 0) {
            $voucher = (object)[
                'discount_type' => 'FLAT', // Assumed for display
                'discount_amount' => $discountAmount
            ];
        }

        // 5. PAYMENTS STATUS
        $allPayments = $booking->payments()->orderBy('payment_date', 'desc')->get();
        $totalPaid = $allPayments->where('payment_status', 'Verified')->sum('total_amount');
        $outstandingBalance = $finalTotal - $totalPaid;

        // 6. UPDATE OR CREATE INVOICE RECORD
        $invoiceData = \App\Models\Invoice::firstOrCreate(
            ['bookingID' => $booking->bookingID],
            [
                'invoice_number' => 'INV-' . date('Ymd') . '-' . $booking->bookingID,
                'issue_date' => now(),
                'totalAmount' => $finalTotal,
            ]
        );
        // Ensure date is updated to now if we are regenerating
        $invoiceData->update(['issue_date' => now()]);

        // 7. GENERATE PDF
        $pdf = Pdf::loadView('pdf.invoice', compact(
            'invoiceData',
            'booking',
            'user',
            'customer',
            'localCustomer',
            'localstudent',
            'internationalCustomer',
            'vehicle',              // <--- Fixes error
            'dailyRate',            // <--- Required by PDF
            'rentalBase',           // <--- Required by PDF
            'addonsBreakdown',      // <--- Required by PDF
            'pickupSurcharge',      // <--- Required by PDF
            'baseAmount',           // <--- Required by PDF
            'voucher',
            'discountAmount',
            'subtotalAfterDiscount',
            'depositAmount',
            'finalTotal',
            'allPayments',
            'totalPaid',
            'outstandingBalance'
        ));

        return $pdf->download('Invoice-' . $booking->bookingID . '.pdf');
    }

    /**
     * Update verified by field for a payment
     */
    public function updateVerifiedBy(Request $request, $id)
    {
        $payment = Payment::where('paymentID', $id)->firstOrFail();
        
        $validated = $request->validate([
            'verified_by' => 'nullable|exists:user,userID',
        ]);
        
        $payment->update([
            'verified_by' => $validated['verified_by'] ?? null,
            'latest_Update_Date_Time' => now(),
        ]);
        
        // Return JSON if requested, otherwise redirect
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Verified by updated successfully.'
            ]);
        }

        return redirect()->route('admin.payments.index')->with('success', 'Verified by updated successfully.');
    }
}
