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
    public function index(Request $request): View
    {
        $search = $request->get('search');
        $filterPaymentDate = $request->get('filter_payment_date');
        $filterPaymentStatus = $request->get('filter_payment_status');
        $filterPaymentIsComplete = $request->get('filter_payment_is_complete');
        $filterPaymentIsVerify = $request->get('payment_isVerify');
        $filterVerifyBy = $request->get('filter_verify_by');
        $query = Payment::with(['booking.customer.user', 'booking.vehicle']);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('bookingID', 'like', "%{$search}%")
                  ->orWhereHas('booking.vehicle', function($vQuery) use ($search) {
                      $vQuery->where('plate_number', 'like', "%{$search}%");
                  });
            });
        }

        if ($filterPaymentDate) {
            $query->whereDate('payment_date', $filterPaymentDate);
        }

        if ($filterPaymentStatus) {
            $query->where('payment_status', $filterPaymentStatus);
        }

        if ($filterPaymentIsComplete !== null && $filterPaymentIsComplete !== '') {
            $query->where('isPayment_complete', (bool)$filterPaymentIsComplete);
        }

        if ($filterPaymentIsVerify !== null && $filterPaymentIsVerify !== '') {
            $query->where('payment_isVerify', (bool)$filterPaymentIsVerify);
        }


        // Filter by verified_by

        if ($filterVerifyBy) {
            $query->where('verified_by', $filterVerifyBy);
        }
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

        $staffUsers = \App\Models\User::where(function($query) {
            $query->whereHas('staff', function($q) {
                $q->where('isActive', true)->whereDoesntHave('runner');
            })->orWhereHas('admin', function($q) {
                $q->where('isActive', true);
            });
        })->where('isActive', true)->with(['staff.runner', 'staff.staffIt', 'admin'])->orderBy('name')->get();

        return view('admin.payments.index', [
            'payments' => $payments,
            'search' => $search,
            'filterPaymentDate' => $filterPaymentDate,
            'filterPaymentStatus' => $filterPaymentStatus,
            'filterPaymentIsComplete' => $filterPaymentIsComplete,
            'filterPaymentIsVerify' => $filterPaymentIsVerify,
            'filterVerifyBy' => $filterVerifyBy,
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

    public function approve($id): \Illuminate\Http\RedirectResponse
    {
        $payment = \App\Models\Payment::with(['booking.vehicle', 'booking.customer.user'])->findOrFail($id);
        $booking = $payment->booking;

        $payment->update([
            'payment_status'          => 'Verified',
            'payment_isVerify'        => 1,
            'latest_Update_Date_Time' => now(),
        ]);

        if ($booking) {
            $booking->update(['booking_status' => 'Confirmed']);
            
            $totalRequired = ($booking->rental_amount ?? 0) + ($booking->deposit_amount ?? 0);
            $totalPaid = $booking->payments()->where('payment_status', 'Verified')->sum('total_amount');
            
            if ($totalPaid >= $totalRequired) {
                try {
                    \App\Models\AdminNotification::where('booking_id', $booking->bookingID)
                        ->where('type', 'upcoming_booking_payment_incomplete')
                        ->where('is_read', false)
                        ->update(['is_read' => true, 'read_at' => now()]);
                } catch (\Exception $e) {
                    Log::warning('Failed to mark notification: ' . $e->getMessage());
                }
            }

            // IMPORTANT: Do NOT change status if booking has progressed beyond early payment stages
            // Status flow: Pending → Reserved → Confirmed → Ongoing → Completed
            // Only update status if booking is in early stages (Pending, Reserved, Confirmed)
            // Never reset status from Ongoing or Completed back to Confirmed/Reserved
            if (!in_array($booking->booking_status, ['Ongoing', 'Completed'])) {
                $grandTotal = $booking->rental_amount; 
                if ($totalPaid >= ($grandTotal - 1.00)) {
                    $booking->update(['booking_status' => 'Confirmed']);
                    $booking->payments()->update(['isPayment_complete' => 1]);
                } else {
                    $booking->update(['booking_status' => 'Reserved']);
                }
            }
        }

        $amountForInvoice = $booking->rental_amount ?? 0;
        $invoiceData = \App\Models\Invoice::firstOrCreate(
            ['bookingID' => $booking->bookingID],
            ['invoice_number' => 'INV-' . date('Ymd') . '-' . $booking->bookingID, 'issue_date' => now(), 'totalAmount' => $amountForInvoice]
        );

        // Loyalty Logic
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
                    'loyaltyCardID' => $card->loyaltyCardID, 'discount_type' => 'PERCENT', 'discount_amount' => 10,
                    'voucher_isActive' => 1, 'created_at' => now(), 'updated_at' => now()
                ]);
                \Illuminate\Support\Facades\DB::table('loyaltycard')->where('loyaltyCardID', $card->loyaltyCardID)->decrement('total_stamps', 5);
                $card = \Illuminate\Support\Facades\DB::table('loyaltycard')->where('loyaltyCardID', $card->loyaltyCardID)->first();
            }
        } catch (\Exception $e) {
            Log::warning('Loyalty Logic Error: ' . $e->getMessage());
        }

        // Wallet Logic
        try {
            $wallet = \Illuminate\Support\Facades\DB::table('walletaccount')->where('customerID', $booking->customerID)->first();
            if ($wallet) {
                $paymentAmount = $payment->total_amount ?? 0;
                $newOutstanding = max(0, $wallet->outstanding_amount - $paymentAmount);
                \Illuminate\Support\Facades\DB::table('walletaccount')->where('walletAccountID', $wallet->walletAccountID)
                    ->update(['outstanding_amount' => $newOutstanding, 'wallet_lastUpdate_Date_Time' => now()]);
            }
        } catch (\Exception $e) {
            Log::warning('Wallet Logic Error: ' . $e->getMessage());
        }

        // Send Email
        $recipientEmail = $booking->customer->user->email ?? null;
        if ($recipientEmail) {
            try {
                $booking->load(['customer.user', 'vehicle', 'payments']);
                $customer = $booking->customer;
                $user = $customer->user;
                $vehicle = $booking->vehicle;

                $localCustomer = \App\Models\Local::where('customerID', $customer->customerID)->first();
                $localstudent  = \App\Models\LocalStudent::where('customerID', $customer->customerID)->first();
                $internationalCustomer = \App\Models\International::where('customerID', $customer->customerID)->first();

                $dailyRate = $vehicle->rental_price;
                $duration = $booking->duration ?? 1;
                $rentalBase = $dailyRate * $duration;

                $addonsString = $booking->addOns_item;
                $addonsArray = $addonsString ? explode(',', $addonsString) : [];
                $addonPrices = ['power_bank' => 5, 'phone_holder' => 5, 'usb_wire' => 3];
                $addonNames = ['power_bank' => 'Power Bank', 'phone_holder' => 'Phone Holder', 'usb_wire' => 'USB Wire'];
                $addonsBreakdown = [];
                $addonsTotal = 0;
                foreach ($addonsArray as $item) {
                    $key = trim($item);
                    if (isset($addonPrices[$key])) {
                        $price = $addonPrices[$key];
                        $total = $price * $duration;
                        $addonsTotal += $total;
                        $addonsBreakdown[] = ['name' => $addonNames[$key] ?? ucwords(str_replace('_', ' ', $key)), 'duration' => $duration, 'daily_price' => $price, 'total' => $total];
                    }
                }

                $pickupSurcharge = ($booking->pickup_point === 'HASTA HQ Office') ? 0 : 10;
                $returnSurcharge = ($booking->return_point === 'HASTA HQ Office') ? 0 : 10;
                
                $pickupCustomLocation = ($booking->pickup_point !== 'HASTA HQ Office') ? $booking->pickup_point : null;
                $returnCustomLocation = ($booking->return_point !== 'HASTA HQ Office') ? $booking->return_point : null;

                $depositAmount = $booking->deposit_amount ?? 50;
                $baseAmount = $rentalBase + $addonsTotal + $pickupSurcharge + $returnSurcharge;
                $calculatedTotalWithDeposit = $baseAmount + $depositAmount;
                $finalTotal = $booking->rental_amount;
                $discountAmount = max(0, $calculatedTotalWithDeposit - $finalTotal);
                $subtotalAfterDiscount = $baseAmount - $discountAmount;

                $voucher = null;
                if ($discountAmount > 0) {
                    $voucher = (object)['discount_type' => 'FLAT', 'discount_amount' => $discountAmount];
                }

                $allPayments = $booking->payments()->orderBy('payment_date', 'desc')->get();
                $totalPaid = $allPayments->where('payment_status', 'Verified')->sum('total_amount');
                $outstandingBalance = $finalTotal - $totalPaid;

                $pdf = Pdf::loadView('pdf.invoice', compact(
                    'invoiceData', 'booking', 'user', 'customer',
                    'localCustomer', 'localstudent', 'internationalCustomer',
                    'vehicle', 'dailyRate', 'rentalBase', 'addonsBreakdown',
                    'pickupSurcharge', 'returnSurcharge', 'pickupCustomLocation', 'returnCustomLocation',
                    'baseAmount', 'voucher', 'discountAmount',
                    'subtotalAfterDiscount', 'depositAmount', 'finalTotal',
                    'allPayments', 'totalPaid', 'outstandingBalance'
                ));

                Mail::to($recipientEmail)->send(new BookingInvoiceMail($booking, $pdf));

            } catch (\Exception $e) {
                Log::error('Mail Error: ' . $e->getMessage());
            }
        }

        return redirect()->route('admin.payments.index')
            ->with('success', 'Payment Verified. Booking status updated.');
    }

    public function updateVerify(Request $request, $id)
    {
        $payment = \App\Models\Payment::with(['booking.customer.user'])->findOrFail($id);
        $booking = $payment->booking;

        $isVerify = $request->input('payment_isVerify') == '1' || $request->input('payment_isVerify') === true;
        $updateData = [
            'payment_isVerify' => $isVerify,
            'latest_Update_Date_Time' => now(),
        ];

        if ($isVerify) {
            $updateData['payment_status'] = 'Verified';
            $payment->update($updateData);

            $invoiceData = \App\Models\Invoice::firstOrCreate(
                ['bookingID' => $booking->bookingID],
                ['invoice_number' => 'INV-' . date('Ymd') . '-' . $booking->bookingID, 'issue_date' => now(), 'totalAmount' => $booking->rental_amount ?? 0]
            );

            if ($booking) {
                $totalRequired = ($booking->rental_amount ?? 0) + ($booking->deposit_amount ?? 0);
                $totalPaid = $booking->payments()->where('payment_status', 'Verified')->sum('total_amount');
                
                if ($totalPaid >= $totalRequired) {
                    try {
                        \App\Models\AdminNotification::where('booking_id', $booking->bookingID)
                            ->where('type', 'upcoming_booking_payment_incomplete')
                            ->where('is_read', false)
                            ->update(['is_read' => true, 'read_at' => now()]);
                    } catch (\Exception $e) {
                        Log::warning('Failed notification update: ' . $e->getMessage());
                    }
                }

                // IMPORTANT: Do NOT change status if booking has progressed beyond early payment stages
                // Status flow: Pending → Reserved → Confirmed → Ongoing → Completed
                // Only update status if booking is in early stages (Pending, Reserved, Confirmed)
                // Never reset status from Ongoing or Completed back to earlier stages
                if (!in_array($booking->booking_status, ['Ongoing', 'Completed'])) {
                    $totalVerifiedPaid = $booking->payments()->where('payment_status', 'Verified')->sum('total_amount');
                    $grandTotal = $booking->rental_amount ?? 0;

                    if ($totalVerifiedPaid >= ($grandTotal - 1.00)) {
                        $booking->update(['booking_status' => 'Confirmed', 'lastUpdateDate' => now()]);
                        $booking->payments()->where('payment_status', 'Verified')->update(['isPayment_complete' => 1]);
                    } elseif ($totalVerifiedPaid > 0) {
                        $booking->update(['booking_status' => 'Reserved', 'lastUpdateDate' => now()]);
                    } else {
                        $booking->update(['booking_status' => 'Pending', 'lastUpdateDate' => now()]);
                    }
                }
            }

            // Loyalty Logic
            try {
                $stamps = 0; 
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
                        'loyaltyCardID' => $card->loyaltyCardID, 'discount_type' => 'PERCENT', 'discount_amount' => 10,
                        'voucher_isActive' => 1, 'created_at' => now(), 'updated_at' => now()
                    ]);
                    \Illuminate\Support\Facades\DB::table('loyaltycard')->where('loyaltyCardID', $card->loyaltyCardID)->decrement('total_stamps', 5);
                    $card = \Illuminate\Support\Facades\DB::table('loyaltycard')->where('loyaltyCardID', $card->loyaltyCardID)->first();
                }
            } catch (\Exception $e) {
                Log::error('Loyalty Error: ' . $e->getMessage());
            }

            // Wallet Logic
            try {
                $wallet = \Illuminate\Support\Facades\DB::table('walletaccount')->where('customerID', $booking->customerID)->first();
                if ($wallet) {
                    $paymentAmount = $payment->total_amount ?? 0;
                    $newOutstanding = max(0, $wallet->outstanding_amount - $paymentAmount);
                    \Illuminate\Support\Facades\DB::table('walletaccount')->where('walletAccountID', $wallet->walletAccountID)
                        ->update(['outstanding_amount' => $newOutstanding, 'wallet_lastUpdate_Date_Time' => now()]);
                }
            } catch (\Exception $e) {
                Log::warning('Wallet Update Failed: ' . $e->getMessage());
            }

            // Email Logic
            $recipientEmail = $booking->customer->user->email ?? null;
            if ($recipientEmail) {
                try {
                    $booking->load(['customer.user', 'vehicle', 'payments']);
                    $customer = $booking->customer;
                    $user = $customer->user;
                    $vehicle = $booking->vehicle;

                    $localCustomer = \App\Models\Local::where('customerID', $customer->customerID)->first();
                    $localstudent  = \App\Models\LocalStudent::where('customerID', $customer->customerID)->first();
                    $internationalCustomer = \App\Models\International::where('customerID', $customer->customerID)->first();

                    $dailyRate = $vehicle->rental_price;
                    $duration = $booking->duration ?? 1;
                    $rentalBase = $dailyRate * $duration;

                    $addonsString = $booking->addOns_item;
                    $addonsArray = $addonsString ? explode(',', $addonsString) : [];
                    $addonPrices = ['power_bank' => 5, 'phone_holder' => 5, 'usb_wire' => 3];
                    $addonNames = ['power_bank' => 'Power Bank', 'phone_holder' => 'Phone Holder', 'usb_wire' => 'USB Wire'];
                    $addonsBreakdown = [];
                    $addonsTotal = 0;
                    foreach ($addonsArray as $item) {
                        $key = trim($item);
                        if (isset($addonPrices[$key])) {
                            $price = $addonPrices[$key];
                            $total = $price * $duration;
                            $addonsTotal += $total;
                            $addonsBreakdown[] = ['name' => $addonNames[$key] ?? ucwords(str_replace('_', ' ', $key)), 'duration' => $duration, 'daily_price' => $price, 'total' => $total];
                        }
                    }

                    // --- THIS IS THE FIX FOR "UNDEFINED VARIABLE" ---
                    $pickupSurcharge = ($booking->pickup_point === 'HASTA HQ Office') ? 0 : 10;
                    $returnSurcharge = ($booking->return_point === 'HASTA HQ Office') ? 0 : 10;
                    
                    $pickupCustomLocation = ($booking->pickup_point !== 'HASTA HQ Office') ? $booking->pickup_point : null;
                    $returnCustomLocation = ($booking->return_point !== 'HASTA HQ Office') ? $booking->return_point : null;

                    $depositAmount = $booking->deposit_amount ?? 50;
                    $baseAmount = $rentalBase + $addonsTotal + $pickupSurcharge + $returnSurcharge;
                    $calculatedTotalWithDeposit = $baseAmount + $depositAmount;
                    $finalTotal = $booking->rental_amount;
                    $discountAmount = max(0, $calculatedTotalWithDeposit - $finalTotal);
                    $subtotalAfterDiscount = $baseAmount - $discountAmount;
                    // ------------------------------------------------

                    $voucher = null;
                    if ($discountAmount > 0) {
                        $voucher = (object)['discount_type' => 'FLAT', 'discount_amount' => $discountAmount];
                    }

                    $allPayments = $booking->payments()->orderBy('payment_date', 'desc')->get();
                    $totalPaid = $allPayments->where('payment_status', 'Verified')->sum('total_amount');
                    $outstandingBalance = $finalTotal - $totalPaid;

                    $pdf = Pdf::loadView('pdf.invoice', compact(
                        'invoiceData', 'booking', 'user', 'customer',
                        'localCustomer', 'localstudent', 'internationalCustomer',
                        'vehicle', 'dailyRate', 'rentalBase', 'addonsBreakdown',
                        'pickupSurcharge', 'returnSurcharge', 'pickupCustomLocation', 'returnCustomLocation',
                        'baseAmount', 'voucher', 'discountAmount',
                        'subtotalAfterDiscount', 'depositAmount', 'finalTotal',
                        'allPayments', 'totalPaid', 'outstandingBalance'
                    ));

                    Mail::to($recipientEmail)->send(new BookingInvoiceMail($booking, $pdf));

                } catch (\Exception $e) {
                    Log::error('Toggle Mail Error: ' . $e->getMessage());
                }
            }

        } else {
            $updateData['payment_status'] = 'Pending';
            $updateData['isPayment_complete'] = false;
            $payment->update($updateData);

            if ($booking) {
                // IMPORTANT: Do NOT change status if booking has progressed beyond early payment stages
                // Status flow: Pending → Reserved → Confirmed → Ongoing → Completed
                // Only update status if booking is in early stages (Pending, Reserved, Confirmed)
                // Never reset status from Ongoing or Completed back to earlier stages
                if (!in_array($booking->booking_status, ['Ongoing', 'Completed'])) {
                    $totalVerifiedPaid = $booking->payments()->where('payment_status', 'Verified')->sum('total_amount');
                    $grandTotal = $booking->rental_amount ?? 0;

                    if ($totalVerifiedPaid >= ($grandTotal - 1.00)) {
                        $booking->update(['booking_status' => 'Confirmed', 'lastUpdateDate' => now()]);
                    } elseif ($totalVerifiedPaid > 0) {
                        $booking->update(['booking_status' => 'Reserved', 'lastUpdateDate' => now()]);
                    } else {
                        $booking->update(['booking_status' => 'Pending', 'lastUpdateDate' => now()]);
                    }
                }
            }
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment verification updated.'
            ]);
        }

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
        $payment = \App\Models\Payment::where('paymentID', $id)->firstOrFail();
        $booking = $payment->booking;
        
        $booking->load(['customer.user', 'vehicle', 'payments']);
        $customer = $booking->customer;
        $user = $customer->user;
        $vehicle = $booking->vehicle;

        $localCustomer = \App\Models\Local::where('customerID', $customer->customerID)->first();
        $localstudent  = \App\Models\LocalStudent::where('customerID', $customer->customerID)->first();
        $internationalCustomer = \App\Models\International::where('customerID', $customer->customerID)->first();

        $dailyRate = $vehicle->rental_price;
        $duration = $booking->duration ?? 1;
        $rentalBase = $dailyRate * $duration;

        $addonsString = $booking->addOns_item; 
        $addonsArray = $addonsString ? explode(',', $addonsString) : [];
        $addonPrices = ['power_bank' => 5, 'phone_holder' => 5, 'usb_wire' => 3];
        $addonNames = ['power_bank' => 'Power Bank', 'phone_holder' => 'Phone Holder', 'usb_wire' => 'USB Wire'];
        $addonsBreakdown = [];
        $addonsTotal = 0;
        foreach ($addonsArray as $item) {
            $key = trim($item);
            if (isset($addonPrices[$key])) {
                $price = $addonPrices[$key];
                $total = $price * $duration;
                $addonsTotal += $total;
                $addonsBreakdown[] = ['name' => $addonNames[$key] ?? ucwords(str_replace('_', ' ', $key)), 'duration' => $duration, 'daily_price' => $price, 'total' => $total];
            }
        }


        // C. Surcharges
        $pickupSurcharge = $booking->pickup_surcharge ?? 0;
        $returnSurcharge = $booking->return_surcharge ?? 0;
        $totalSurcharge = $pickupSurcharge + $returnSurcharge;
        $pickupCustomLocation = $booking->pickup_custom_location ?? null;
        $returnCustomLocation = $booking->return_custom_location ?? null;

        $pickupSurcharge = ($booking->pickup_point === 'HASTA HQ Office') ? 0 : 10;
        $returnSurcharge = ($booking->return_point === 'HASTA HQ Office') ? 0 : 10;

        // 4. CALCULATE TOTALS & DISCOUNT
        $baseAmount = $rentalBase + $addonsTotal + $totalSurcharge; // Subtotal before discount

        $depositAmount = $booking->deposit_amount ?? 50; 
        $baseAmount = $rentalBase + $addonsTotal + $pickupSurcharge + $returnSurcharge; 

        $calculatedTotalWithDeposit = $baseAmount + $depositAmount;
        $finalTotal = $booking->rental_amount; 
        $discountAmount = max(0, $calculatedTotalWithDeposit - $finalTotal);
        $subtotalAfterDiscount = $baseAmount - $discountAmount;

        $voucher = null;
        if ($discountAmount > 0) {
            $voucher = (object)['discount_type' => 'FLAT', 'discount_amount' => $discountAmount];
        }

        $allPayments = $booking->payments()->orderBy('payment_date', 'desc')->get();
        $totalPaid = $allPayments->where('payment_status', 'Verified')->sum('total_amount');
        $outstandingBalance = $finalTotal - $totalPaid;

        $invoiceData = \App\Models\Invoice::firstOrCreate(
            ['bookingID' => $booking->bookingID],
            ['invoice_number' => 'INV-' . date('Ymd') . '-' . $booking->bookingID, 'issue_date' => now(), 'totalAmount' => $finalTotal]
        );
        $invoiceData->update(['issue_date' => now()]);

        // 7. GENERATE PDF
        $rentalAmount = $booking->rental_amount; // Legacy support
        $pdf = Pdf::loadView('pdf.invoice', compact(
            'invoiceData',
            'booking',
            'user',
            'customer',
            'localCustomer',
            'localstudent',
            'internationalCustomer',
            'vehicle',
            'dailyRate',
            'rentalBase',
            'pickupSurcharge',
            'returnSurcharge',
            'totalSurcharge',
            'pickupCustomLocation',
            'returnCustomLocation',
            'addonsBreakdown',
            'baseAmount',
            'voucher',
            'discountAmount',
            'subtotalAfterDiscount',
            'depositAmount',
            'finalTotal',
            'allPayments',
            'totalPaid',
            'outstandingBalance',
            'rentalAmount'
        ));

        return $pdf->download('Invoice-' . $booking->bookingID . '.pdf');
    }

    public function updateVerifiedBy(Request $request, $id)
    {
        $payment = Payment::where('paymentID', $id)->firstOrFail();
        
        $validated = $request->validate(['verified_by' => 'nullable|exists:user,userID']);
        
        $payment->update([
            'verified_by' => $validated['verified_by'] ?? null,
            'latest_Update_Date_Time' => now(),
        ]);
        
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Verified by updated successfully.']);
        }

        return redirect()->route('admin.payments.index')->with('success', 'Verified by updated successfully.');
    }


    /**
     * Export payments as PDF
     */
    public function exportPdf(Request $request)
    {
        $query = $this->buildPaymentQuery($request);
        $payments = $query->get();

        $pdf = Pdf::loadView('admin.payments.export-pdf', [
            'payments' => $payments,
            'filters' => $request->all(),
        ]);

        return $pdf->download('payments-export-' . date('Y-m-d') . '.pdf');
    }

    /**
     * Export payments as Excel (CSV)
     */
    public function exportExcel(Request $request)
    {
        $query = $this->buildPaymentQuery($request);
        $payments = $query->get();

        $data = $payments->map(function($payment) {
            $booking = $payment->booking;
            $customer = $booking->customer ?? null;
            $user = $customer->user ?? null;
            $vehicle = $booking->vehicle ?? null;
            $verifiedBy = $payment->verified_by ? \App\Models\User::find($payment->verified_by) : null;

            return [
                'Payment ID' => $payment->paymentID,
                'Booking ID' => $payment->bookingID,
                'Customer Name' => $user->name ?? 'N/A',
                'Vehicle Plate' => $vehicle->plate_number ?? 'N/A',
                'Bank Name' => $payment->payment_bank_name ?? 'N/A',
                'Account No' => $payment->payment_bank_account_no ?? 'N/A',
                'Payment Date' => $payment->payment_date ? $payment->payment_date->format('Y-m-d H:i') : 'N/A',
                'Amount' => number_format($payment->total_amount ?? 0, 2),
                'Transaction Reference' => $payment->transaction_reference ?? 'N/A',
                'Payment Status' => $payment->payment_status ?? 'N/A',
                'Is Verified' => $payment->payment_isVerify ? 'Yes' : 'No',
                'Is Complete' => $payment->isPayment_complete ? 'Yes' : 'No',
                'Verified By' => $verifiedBy->name ?? 'N/A',
                'Last Updated' => $payment->latest_Update_Date_Time ? $payment->latest_Update_Date_Time->format('Y-m-d H:i') : 'N/A',
            ];
        });

        $filename = 'payments-export-' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            if ($data->isNotEmpty()) {
                fputcsv($file, array_keys($data->first()));
            }
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Build payment query with filters (reused for export functions)
     */
    private function buildPaymentQuery(Request $request)
    {
        $search = $request->get('search');
        $filterPaymentDate = $request->get('filter_payment_date');
        $filterPaymentStatus = $request->get('filter_payment_status');
        $filterPaymentIsComplete = $request->get('filter_payment_is_complete');
        $filterPaymentIsVerify = $request->get('payment_isVerify');
        $filterVerifyBy = $request->get('filter_verify_by');
        
        $query = Payment::with(['booking.customer.user', 'booking.vehicle']);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('bookingID', 'like', "%{$search}%")
                  ->orWhereHas('booking.vehicle', function($vQuery) use ($search) {
                      $vQuery->where('plate_number', 'like', "%{$search}%");
                  });
            });
        }

        if ($filterPaymentDate) {
            $query->whereDate('payment_date', $filterPaymentDate);
        }

        if ($filterPaymentStatus) {
            $query->where('payment_status', $filterPaymentStatus);
        }

        if ($filterPaymentIsComplete !== null && $filterPaymentIsComplete !== '') {
            $query->where('isPayment_complete', (bool)$filterPaymentIsComplete);
        }

        if ($filterPaymentIsVerify !== null && $filterPaymentIsVerify !== '') {
            $query->where('payment_isVerify', (bool)$filterPaymentIsVerify);
        }

        if ($filterVerifyBy) {
            $query->where('verified_by', $filterVerifyBy);
        }

        return $query->orderBy('bookingID', 'desc')->orderBy('paymentID', 'desc');
    }
}



