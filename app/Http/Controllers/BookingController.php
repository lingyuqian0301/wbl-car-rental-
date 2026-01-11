<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Local;
use App\Models\International;
use App\Models\LocalStudent;
use App\Models\InternationalStudent;
use App\Models\StudentDetails;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\Vehicle;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Added for logging errors

class BookingController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Display a listing of the user's bookings.
     */
    public function index()
    {
        try {
            $customer = \App\Models\Customer::where('userID', auth()->user()->userID)->first();

            if (!$customer) {
                // Not an error, just an empty state
                return view('bookings.index', ['bookings' => collect([])]);
            }

            $bookings = \App\Models\Booking::where('customerID', $customer->customerID)
                        ->with(['vehicle', 'payments'])
                        ->orderBy('bookingID', 'desc')
                        ->paginate(10);

            // Log payment data for debugging
            foreach ($bookings as $booking) {
                Log::info('Booking ID: ' . $booking->bookingID);
                Log::info('Payments: ' . $booking->payments->toJson());
            }

            // Update status logic to handle bookings without payments explicitly
            $status = 'Comfirmed';
            if ($bookings->isNotEmpty()) {
                $firstBooking = $bookings->first();
                if ($firstBooking->payments->where('status', 'Pending')->isNotEmpty() || $firstBooking->payments->where('status', 'Rejected')->isNotEmpty()) {
                    $status = 'Pending';
                } elseif ($firstBooking->status === 'Confirmed') {
                    $status = 'Confirmed';
                } elseif ($firstBooking->status === 'Cancelled') {
                    $status = 'Cancelled';
                }
            }

            return view('bookings.index', [
                'bookings' => $bookings,
                'status' => $status,
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching bookings: ' . $e->getMessage());
            return redirect()->route('home')->with('error', 'Unable to load bookings. Please try again later.');
        }
    }

    /**
     * Display the specified booking.
     */
    public function show(Booking $booking): View
        {
        // --- START OF FIX ---
        $isAuthorized = false;

        // 1. Check if the booking belongs to the logged-in user
        // We traverse: Booking -> Customer -> User ID
        if ($booking->customer && $booking->customer->userID === Auth::user()->userID) {
            $isAuthorized = true;
        }

        // 2. Allow Admins to view any booking (Optional but recommended)
        if (Auth::user()->isAdmin() || Auth::user()->isStaff()) {
            $isAuthorized = true;
        }

    if (!$isAuthorized) {
        abort(403, 'You are not authorized to view this booking.');
    }

        try {
            $booking->load(['vehicle', 'payments']);

            $hasVerifiedPayment = $booking->payments
                ->where('payment_status', 'Verified')
                ->isNotEmpty();

            return view('bookings.show', [
                'booking' => $booking,
                'hasVerifiedPayment' => $hasVerifiedPayment,
            ]);
        } catch (\Exception $e) {
            Log::error('Error showing booking details: ' . $e->getMessage());
            abort(500, 'System error while loading booking details.');
        }
    }
/**
     * Show the booking form/details for a specific vehicle.
     * Corresponds to URL: http://127.0.0.1:8000/vehicles/{id}
     */
    public function create(Request $request, $vehicleID)
    {
        // 1. Fetch Vehicle Data
        $vehicle = Vehicle::findOrFail($vehicleID);

        // 2. Get Dates from URL (e.g. ?start_date=2026-01-10)
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        // ==============================================
        // 3. VOUCHER CHECK LOGIC
        // ==============================================
        $activeVoucher = null;

        if (Auth::check()) {
            $user = Auth::user();

            // Ensure user has a customer profile
            if ($user->customer) {
                // Look for a voucher that belongs to this customer AND is active
                $activeVoucher = DB::table('voucher')
                    ->join('loyaltycard', 'voucher.loyaltyCardID', '=', 'loyaltycard.loyaltyCardID')
                    ->where('loyaltycard.customerID', $user->customer->customerID)
                    ->where('voucher.voucher_isActive', 1) // Must be unspent
                    ->select('voucher.*')
                    ->first();
            }
        }

        // 4. Return the View with the Voucher data
        return view('bookings.create', [
            'vehicle' => $vehicle,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'activeVoucher' => $activeVoucher // <--- This enables the alert in your view
        ]);
    }
   public function store(Request $request, $vehicleID)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            $request->session()->put('url.intended', url()->previous());
            return redirect()->route('login')->with('error', 'Please sign in to proceed with booking.');
        }

        // ===== PROFILE VALIDATION =====
        // Check if user has completed required profile fields before booking
        $user = Auth::user();
        $customer = Customer::where('userID', $user->userID)->first();

        if (!$customer || $this->isProfileIncomplete($customer)) {
            return redirect()
                ->route('profile.edit')
                ->with('warning', 'Please complete your profile information before proceeding with booking.');
        }

        // Validate form inputs
        $request->validate([
            'start_date'    => 'required|date|after_or_equal:today',
            'start_time'    => 'required|date_format:H:i',
            'end_date'      => 'required|date|after:start_date',
            'end_time'      => 'required|date_format:H:i',
            'pickup_point'  => 'required|string|max:255',
            'return_point'  => 'required|string|max:100',
            'pickup_surcharge' => 'nullable|numeric',
        ]);

        // Check for date overlap with existing bookings
     $startDateTime = $request->start_date . ' ' . $request->start_time;
$endDateTime   = $request->end_date   . ' ' . $request->end_time;


        $overlap = Booking::where('vehicleID', $vehicleID)
            ->where('booking_status', '!=', 'Cancelled')
            ->where(function ($q) use ($startDateTime, $endDateTime) {
                $q->where('rental_start_date', '<=', $endDateTime)
                  ->where('rental_end_date', '>=', $startDateTime);
            })
            ->exists();

        if ($overlap) {
            return back()->withErrors(['dates' => 'These dates are unavailable. Please select different dates.']);
        }

        // Calculate duration
        $duration = Carbon::parse($request->start_date)
            ->diffInDays(Carbon::parse($request->end_date)) + 1;

        // Get vehicle and addons
        $vehicle = Vehicle::findOrFail($vehicleID);
        $vehiclePrice = $vehicle->rental_price;

        $addons = $request->addons ?? [];
        $addonPrices = [
            'power_bank'  => 5,
            'phone_holder' => 5,
            'usb_wire'    => 3,
        ];

        $addonsPerDay = 0;
        foreach ($addons as $addon) {
            $addonsPerDay += $addonPrices[$addon] ?? 0;
        }

        // Calculate total (matching booking page: base + addons × duration + surcharge + deposit)
        $pickupSurcharge = (float) ($request->pickup_surcharge ?? 0);
        $depositAmount = 50; // Fixed deposit amount
        $totalAmount = ($vehiclePrice + $addonsPerDay) * $duration + $pickupSurcharge + $depositAmount;

        // Store booking data in session
        $bookingData = [
            'vehicleID' => $vehicleID,
            'rental_start_date' => $request->start_date . ' ' . $request->start_time,
            'rental_end_date' => $request->end_date . ' ' . $request->end_time,
            'pickup_point' => $request->pickup_point,
            'return_point' => $request->return_point,
            'duration' => $duration,
            'addOns_item' => implode(',', $addons),
            'rental_amount' => $totalAmount,
            'pickup_surcharge' => $pickupSurcharge,
            'booking_status' => 'Pending',
        ];

        session(['booking_data' => $bookingData]);

        // Redirect to confirmation page
        return redirect()->route('booking.confirm');
    }

    /**
     * Get booked dates for a vehicle (for Flatpickr calendar)
     */
    public function getBookedDates($vehicleID)
    {
        $bookings = Booking::where('vehicleID', $vehicleID)
            ->where('booking_status', '!=', 'Cancelled')
            ->select('rental_start_date', 'rental_end_date')
            ->get();

        $bookedRanges = [];
        foreach ($bookings as $booking) {
            $start = Carbon::parse($booking->rental_start_date);
            $end = Carbon::parse($booking->rental_end_date);

            // Create array of all dates in range
            while ($start->lte($end)) {
                $bookedRanges[] = $start->format('Y-m-d');
                $start->addDay();
            }
        }

        return response()->json([
            'booked_dates' => $bookedRanges
        ]);
    }

   public function confirm()
    {
        try {
            $bookingData = session('booking_data');
            if (!$bookingData) {
                return redirect('/')->with('error', 'Session expired.');
            }

            $vehicle = Vehicle::findOrFail($bookingData['vehicleID']);
            $user = Auth::user();
            $customer = $user->customer;

            // === VOUCHER LOGIC ===
            $activeVoucher = null;
            $discountAmount = 0;

            if ($customer) {
                // 1. Check if customer has loyalty card and enough stamps to auto-create voucher
                $loyaltyCard = DB::table('loyaltycard')
                    ->where('customerID', $customer->customerID)
                    ->first();

                // Auto-create voucher if customer has 5+ stamps and no active voucher
                if ($loyaltyCard && $loyaltyCard->total_stamps >= 5) {
                    // Check if customer already has an active voucher
                    $existingVoucher = DB::table('voucher')
                        ->join('loyaltycard', 'voucher.loyaltyCardID', '=', 'loyaltycard.loyaltyCardID')
                        ->where('loyaltycard.customerID', $customer->customerID)
                        ->where('voucher.voucher_isActive', 1)
                        ->select('voucher.*')
                        ->first();

                    // If no active voucher exists, create one
                    if (!$existingVoucher) {
                        DB::table('voucher')->insert([
                            'loyaltyCardID' => $loyaltyCard->loyaltyCardID,
                            'discount_type' => 'PERCENT',
                            'discount_amount' => 10, // 10% discount
                            'voucher_isActive' => 1,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }

                // 2. Get active voucher for this customer
                $activeVoucher = DB::table('voucher')
                    ->join('loyaltycard', 'voucher.loyaltyCardID', '=', 'loyaltycard.loyaltyCardID')
                    ->where('loyaltycard.customerID', $customer->customerID)
                    ->where('voucher.voucher_isActive', 1)
                    ->select('voucher.*')
                    ->first();
            }

            // Recalculate Logic
            $rentalPrice = $vehicle->rental_price * $bookingData['duration'];

            // Calculate addons total
            $addonsArray = !empty($bookingData['addOns_item']) ? explode(',', $bookingData['addOns_item']) : [];
            $addonPrices = ['power_bank' => 5, 'phone_holder' => 5, 'usb_wire' => 3];
            $addonsTotal = 0;
            foreach ($addonsArray as $addon) {
                if (isset($addonPrices[$addon])) {
                    $addonsTotal += $addonPrices[$addon] * $bookingData['duration'];
                }
            }

            // Calculate base amount (rental + addons + surcharge) - deposit is not included in discount calculation
            $baseAmount = $rentalPrice + $addonsTotal + ($bookingData['pickup_surcharge'] ?? 0);

            // Apply 10% Discount if voucher exists
            if ($activeVoucher && $activeVoucher->discount_type === 'PERCENT' && $activeVoucher->discount_amount == 10) {
                $discountAmount = $baseAmount * 0.10; // 10% discount
            } elseif ($activeVoucher && $activeVoucher->discount_type === 'FLAT') {
                $discountAmount = min($activeVoucher->discount_amount, $baseAmount); // Flat discount capped at base amount
            }

            // Re-calculate Total
            // Original total includes: rental + addons + surcharge + deposit
            // Discount applies to: rental + addons + surcharge (not deposit)
            // Final total = baseAmount - discountAmount + deposit
            $depositAmount = 50; // Fixed deposit amount
            $finalTotal = max(0, $baseAmount - $discountAmount + $depositAmount);

            // Update session for finalize step
            $bookingData['final_total'] = $finalTotal; // Store separately to avoid confusion
            $bookingData['discount_amount'] = $discountAmount;
            $bookingData['base_amount'] = $baseAmount; // Store base amount for reference
            $bookingData['deposit_amount'] = $depositAmount; // Store deposit amount
            session(['booking_data' => $bookingData]);

            // Addons Details for View
            $addonsArray = !empty($bookingData['addOns_item']) ? explode(',', $bookingData['addOns_item']) : [];
            $addonDetails = [];
            $addonPrices = ['power_bank' => 5, 'phone_holder' => 5, 'usb_wire' => 3];
            $addonNames = ['power_bank' => 'Power Bank', 'phone_holder' => 'Phone Holder', 'usb_wire' => 'USB Wire'];

            foreach ($addonsArray as $addon) {
                if (isset($addonPrices[$addon])) {
                    $addonDetails[] = [
                        'name' => $addonNames[$addon],
                        'price' => $addonPrices[$addon],
                        'total' => $addonPrices[$addon] * $bookingData['duration']
                    ];
                }
            }

            // Standardize for View
            $standardizedBookingData = [
                'rental_start_date' => $bookingData['rental_start_date'],
                'rental_end_date' => $bookingData['rental_end_date'],
                'duration' => $bookingData['duration'],
                'pickup_point' => $bookingData['pickup_point'],
                'return_point' => $bookingData['return_point'],
                'total_amount' => $finalTotal, // Show discounted price
                'vehicleID' => $bookingData['vehicleID'],
                'original_rental' => $rentalPrice
            ];

            $depositAmount = 50;
            $walletAccount = $customer ? $customer->walletAccount : null;
            $walletBalance = $walletAccount ? $walletAccount->wallet_balance : 0;
            $canSkipDeposit = $this->paymentService->canSkipDepositWithWallet($user->userID, $depositAmount);

            return view('bookings.confirm', [
                'bookingData' => $standardizedBookingData,
                'vehicle' => $vehicle,
                'addons' => $addonDetails,
                'depositAmount' => $depositAmount,
                'walletBalance' => $walletBalance,
                'canSkipDeposit' => $canSkipDeposit,
                'activeVoucher' => $activeVoucher,
                'discountAmount' => $discountAmount,
                'baseAmount' => $baseAmount,
                'pickupSurcharge' => $bookingData['pickup_surcharge'] ?? 0
            ]);

        } catch (\Exception $e) {
            Log::error('Confirmation Page Error: ' . $e->getMessage());
            return redirect('/')->with('error', 'Error loading confirmation page.');
        }
    }

public function finalize(Request $request)
    {
        Log::info('Finalize booking called', ['request' => $request->all()]);

        try {
            // Validation
            $request->validate([
                'vehicle_id' => 'required|integer',
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'pickup_point' => 'required|string',
                'return_point' => 'required|string',
            ]);

            DB::beginTransaction();

            $userID = Auth::user()->userID;
            $customer = Customer::where('userID', $userID)->first();

            // 1. RE-CHECK VOUCHER
            $activeVoucher = DB::table('voucher')
                ->join('loyaltycard', 'voucher.loyaltyCardID', '=', 'loyaltycard.loyaltyCardID')
                ->where('loyaltycard.customerID', $customer->customerID)
                ->where('voucher.voucher_isActive', 1)
                ->select('voucher.*')
                ->first();

            // 2. USE SESSION DATA FOR TOTAL
            $bookingData = session('booking_data');
            $finalRentalAmount = $bookingData['final_total'] ?? $request->total_amount;
            $requiredDeposit = $bookingData['deposit_amount'] ?? 50; // Get deposit amount

            // =========================================================
            // NEW LOGIC: CHECK WALLET HOLDING & AUTO-RESERVE
            // =========================================================
            $bookingStatus = 'Pending'; // Default
            $depositMessage = '';
            
            // Get Wallet
            $wallet = DB::table('walletaccount')->where('customerID', $customer->customerID)->first();

            // Check if wallet has enough for deposit
            if ($wallet && $wallet->wallet_balance >= $requiredDeposit) {
                // A. Deduct from Wallet (Holding Logic)
                DB::table('walletaccount')
                    ->where('walletAccountID', $wallet->walletAccountID)
                    ->update([
                        'wallet_balance' => $wallet->wallet_balance - $requiredDeposit,
                        'wallet_lastUpdate_Date_Time' => now()
                    ]);

                // B. Set Status to RESERVED (Skip Admin)
                $bookingStatus = 'Reserved';
                $depositMessage = ' Deposit was auto-deducted from your wallet.';
                
                Log::info("Booking Auto-Reserved. RM $requiredDeposit deducted from Wallet ID: $wallet->walletAccountID");
            }
            // =========================================================
            // END NEW LOGIC
            // =========================================================

            // 3. CREATE BOOKING
            $booking = new Booking();
            $booking->customerID = $customer->customerID;
            $booking->vehicleID = $request->vehicle_id;
            $booking->rental_start_date = $request->start_date . ' ' . $request->start_time;
            $booking->rental_end_date = $request->end_date . ' ' . $request->end_time;
            $booking->duration = Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date)) + 1;
            $booking->pickup_point = $request->pickup_point;
            $booking->return_point = $request->return_point;
            $booking->rental_amount = $finalRentalAmount;
            $booking->addOns_item = $bookingData['addOns_item'] ?? null;
            $booking->deposit_amount = $requiredDeposit;
            
            // USE THE STATUS DETERMINED ABOVE
            $booking->booking_status = $bookingStatus; 
            
            $booking->lastUpdateDate = now();

            $booking->save();

            // 4. DEACTIVATE VOUCHER AND DEDUCT 5 STAMPS
            if ($activeVoucher && isset($bookingData['discount_amount']) && $bookingData['discount_amount'] > 0) {
                DB::table('voucher')
                    ->where('voucherID', $activeVoucher->voucherID)
                    ->update(['voucher_isActive' => 0]);

                $loyaltyCard = DB::table('loyaltycard')
                    ->where('loyaltyCardID', $activeVoucher->loyaltyCardID)
                    ->first();

                if ($loyaltyCard && $loyaltyCard->total_stamps >= 5) {
                    DB::table('loyaltycard')
                        ->where('loyaltyCardID', $activeVoucher->loyaltyCardID)
                        ->update([
                            'total_stamps' => max(0, $loyaltyCard->total_stamps - 5),
                            'loyalty_last_updated' => now()
                        ]);
                }
            }

            // 5. WALLET & NOTIFICATION (Existing helper)
            $this->updateWalletAndNotify($customer, $booking);

            session()->forget('booking_data');
            DB::commit();

            // Redirect based on status
            if ($booking->booking_status === 'Reserved') {
                // If auto-approved, go to Bookings List or Payment for Rental Fee
                return redirect()->route('bookings.index')
                    ->with('success', 'Booking Confirmed (Reserved)!' . $depositMessage);
            } else {
                // If Pending, go to Payment page to pay Deposit manually
                return redirect()->route('payments.create', ['booking' => $booking->bookingID])
                    ->with('success', 'Booking placed! Please pay the deposit.');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Finalize Booking Error: ' . $e->getMessage());
            return redirect()->route('booking.confirm')->with('error', 'System Error. Please try again.');
        }
    }

    /**
     * Download the invoice PDF for a specific booking.
     */
   public function downloadInvoice($id)
    {
        // 1. Fetch Booking with all necessary relationships
        $booking = \App\Models\Booking::with([
            'invoice', 
            'payments', 
            'vehicle', 
            'customer.local', 
            'customer.international'
        ])->findOrFail($id);

        // 2. Security Check
        if ($booking->customer->userID !== \Illuminate\Support\Facades\Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // 3. Check Invoice
        $invoiceData = $booking->invoice;
        if (!$invoiceData) {
            return redirect()->back()->with('error', 'Invoice has not been generated yet.');
        }

        // 4. PREPARE VIEW DATA
        // ---------------------------------------------
        
        // A. Objects & Flags
        $vehicle = $booking->vehicle;
        $customer = $booking->customer;
        $localCustomer = $customer->local ?? null;
        $internationalCustomer = $customer->international ?? null;
        $localstudent = $localCustomer ? true : false;
        
        $allPayments = $booking->payments; 

        // B. Financial Basics
        $dailyRate = $vehicle->rental_price ?? 0;
        $duration = $booking->duration ?? 1;
        $rentalBase = $dailyRate * $duration;
        $pickupSurcharge = $booking->pickup_surcharge ?? 0;
        $depositAmount = 50; 

        // C. Add-ons Breakdown
        $addonsBreakdown = [];
        $addonsTotal = 0;
        if (!empty($booking->addOns_item)) {
            $addonItems = explode(',', $booking->addOns_item);
            $addonPrices = ['power_bank' => 5, 'phone_holder' => 5, 'usb_wire' => 3];
            $addonNames  = ['power_bank' => 'Power Bank', 'phone_holder' => 'Phone Holder', 'usb_wire' => 'USB Wire'];

            foreach ($addonItems as $item) {
                if (isset($addonPrices[$item])) {
                    $itemTotal = $addonPrices[$item] * $duration;
                    $addonsTotal += $itemTotal;
                    
                    $addonsBreakdown[] = [
                        'name'  => $addonNames[$item] ?? ucfirst(str_replace('_', ' ', $item)),
                        'price' => $addonPrices[$item],
                        'total' => $itemTotal
                    ];
                }
            }
        }

        // D. Base Amount (Before Discount & Deposit)
        $baseAmount = $rentalBase + $addonsTotal + $pickupSurcharge;

        // E. Discount Logic
        $actualFinalTotal = $booking->rental_amount; 
        $expectedTotal = $baseAmount + $depositAmount;
        $diff = $expectedTotal - $actualFinalTotal;

        $voucher = null;
        $discountAmount = 0;

        if ($diff > 0.01) {
            $discountAmount = $diff;
            $voucher = (object)[
                'discount_type' => 'PERCENT', 
                'discount_amount' => 'LOYALTY'
            ];
        }

        // F. Final Totals
        $finalTotal = $actualFinalTotal; 
        $totalPaid = $booking->payments->where('payment_status', 'Verified')->sum('total_amount');
        
        // G. Outstanding Balance (FIX FOR YOUR ERROR)
        // Calculates how much is left to pay. Max(0, ...) ensures it doesn't show negative if overpaid.
        $outstandingBalance = max(0, $finalTotal - $totalPaid);

        $rentalAmount = $booking->rental_amount; // Legacy support

        // 5. Generate PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice', compact(
            'booking', 
            'invoiceData', 
            'vehicle',
            'customer',
            'localCustomer', 
            'internationalCustomer', 
            'localstudent',
            // Financial Data
            'dailyRate',
            'rentalBase',
            'pickupSurcharge',
            'addonsBreakdown',
            'baseAmount',
            'voucher',
            'discountAmount',
            'depositAmount', 
            'finalTotal',
            'allPayments',
            'totalPaid',
            'outstandingBalance', // <--- FIX: Defined here
            'rentalAmount'
        ));

        return $pdf->download('Invoice-' . $booking->bookingID . '.pdf');
    }
    /**
     * Cancel a booking with "12-Hour Rule" logic.
     */
public function cancel(Request $request, $id)
    {
        // 1. Fetch booking with dependencies
        $booking = Booking::with(['customer.walletAccount', 'payments', 'vehicle'])->findOrFail($id);

        // 2. Security Check
        if ($booking->customer->userID !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // 3. Status Check
        if (in_array($booking->booking_status, ['Cancelled', 'Completed'])) {
            return back()->with('error', 'Booking is already cancelled or completed.');
        }

        // 4. THE 12-HOUR RULE Logic
        $pickupTime = Carbon::parse($booking->rental_start_date);
        $hoursUntilPickup = now()->diffInHours($pickupTime, false); // false allows negative numbers

        // Check if user has actually paid anything (Verified payments only)
        $totalPaid = $booking->payments->where('payment_status', 'Verified')->sum('total_amount');
        $hasPaid = $totalPaid > 0;

        $message = "Booking cancelled successfully.";

        if ($hasPaid) {
            if ($hoursUntilPickup < 12) {
                // === SCENARIO A: BURN DEPOSIT (< 12 Hours) ===
                // We DO NOT refund the wallet. The money is forfeited.
                $message = "Booking cancelled. Since this is within 12 hours of pickup, your payment of RM " . number_format($totalPaid, 2) . " has been forfeited (non-refundable).";
                
                Log::info("Booking #{$booking->bookingID} cancelled late (<12h). RM {$totalPaid} forfeited.");
            } else {
                // === SCENARIO B: REFUND DEPOSIT (> 12 Hours) ===
                // Refund money to Wallet
                $wallet = $booking->customer->walletAccount;
                if ($wallet) {
                    $wallet->wallet_balance += $totalPaid;
                    $wallet->save();

                    // Create Wallet Transaction Record
                    \App\Models\WalletTransaction::create([
                        'walletAccountID'  => $wallet->walletAccountID,
                        'amount'           => $totalPaid,
                        'transaction_type' => 'Refund',
                        'description'      => "Refund for cancellation of Booking #{$booking->bookingID}",
                        'transaction_date' => now(),
                        'reference_id'     => $booking->bookingID
                    ]);

                    $message = "Booking cancelled. RM " . number_format($totalPaid, 2) . " has been refunded to your wallet.";
                }
            }
        }

        // 5. Update Status
        $booking->booking_status = 'Cancelled';
        $booking->save();

        // 6. Admin Notification
        try {
            $vehicle = $booking->vehicle;
            $info = $vehicle ? "{$vehicle->vehicle_brand} {$vehicle->vehicle_model}" : 'Vehicle';
            
            \App\Models\AdminNotification::create([
                'type' => 'booking_cancelled',
                'notifiable_type' => 'admin',
                'user_id' => Auth::id(),
                'booking_id' => $booking->bookingID,
                'message' => "Booking #{$booking->bookingID} cancelled by user. ({$info})",
                'is_read' => false,
            ]);
        } catch (\Exception $e) {
            Log::warning('Notification Error: ' . $e->getMessage());
        }

        return back()->with($hoursUntilPickup < 12 ? 'warning' : 'success', $message);
    }

    /**
     * Show form to extend booking.
     */
public function showExtendForm($id)
    {
        // 1. Fetch booking with payments and vehicle
        $booking = Booking::with(['vehicle', 'payments'])->findOrFail($id);

        // 2. Security: Ensure it belongs to the user
        if ($booking->customer->userID !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // 3. Logic: Allow Extend ONLY if status is NOT Confirmed AND Payment is NOT Verified
        $hasVerifiedPayment = $booking->payments->contains('payment_status', 'Verified');

        if ($booking->booking_status === 'Confirmed') {
            return redirect()->route('bookings.index')->with('error', 'Cannot extend a booking that has already been Confirmed.');
        }

        if ($hasVerifiedPayment) {
            return redirect()->route('bookings.index')->with('error', 'Cannot extend a booking once payment has been Verified.');
        }

        if (in_array($booking->booking_status, ['Cancelled', 'Completed'])) {
            return redirect()->route('bookings.index')->with('error', 'Cannot extend a completed or cancelled booking.');
        }

        return view('bookings.extend', compact('booking'));
    }

    /**
     * Check if the customer's profile is incomplete (missing required fields)
     * Required fields for booking:
     * - phone_number (from Customer table)
     * - customer_license (from Customer table)
     * - address (from Customer table)
     * - identity information via Local or International:
     *   - ic_no (Local) OR passport_no (International)
     *   - stateOfOrigin/countryOfOrigin
     * - Student information (if applicable):
     *   - matric_number
     *   - college, faculty, programme (from StudentDetails)
     */
    private function isProfileIncomplete(Customer $customer): bool
    {
        // 1. Check basic customer fields are filled
        if (empty($customer->phone_number)) {
            return true; // Missing phone_number
        }
        if (empty($customer->customer_license)) {
            return true; // Missing customer_license
        }
        if (empty($customer->address)) {
            return true; // Missing address
        }

        // 2. Check identity information (Local or International)
        $local = \App\Models\Local::where('customerID', $customer->customerID)->first();
        $international = \App\Models\International::where('customerID', $customer->customerID)->first();

        if (!$local && !$international) {
            return true; // No identity record created
        }

        // Check Local identity
        if ($local) {
            if (empty($local->ic_no)) {
                return true; // Missing IC number
            }
            if (empty($local->stateOfOrigin)) {
                return true; // Missing state of origin
            }

            // For Local users, check if they're students and validate student fields
            $localStudent = \App\Models\LocalStudent::where('customerID', $customer->customerID)->first();
            if ($localStudent && !empty($localStudent->matric_number)) {
                $studentDetails = \App\Models\StudentDetails::where('matric_number', $localStudent->matric_number)->first();
                if ($studentDetails) {
                    if (empty($studentDetails->college) || empty($studentDetails->faculty) || empty($studentDetails->programme)) {
                        return true; // Student fields incomplete
                    }
                } else {
                    return true; // Matric number provided but no student details found
                }
            }
        }

        // Check International identity
        if ($international) {
            if (empty($international->passport_no)) {
                return true; // Missing passport number
            }
            if (empty($international->countryOfOrigin)) {
                return true; // Missing country of origin
            }

            // For International students, check student fields
            $intlStudent = \App\Models\InternationalStudent::where('customerID', $customer->customerID)->first();
            if ($intlStudent && !empty($intlStudent->matric_number)) {
                $studentDetails = \App\Models\StudentDetails::where('matric_number', $intlStudent->matric_number)->first();
                if ($studentDetails) {
                    if (empty($studentDetails->college) || empty($studentDetails->faculty) || empty($studentDetails->programme)) {
                        return true; // Student fields incomplete
                    }
                } else {
                    return true; // Matric number provided but no student details found
                }
            }
        }

        // If all required fields are present, profile is complete
        return false;
    }

    private function updateWalletAndNotify(Customer $customer, Booking $booking)
    {
        // 1. Create Admin Notification for the new booking
        try {
            $vehicle = $booking->vehicle;
            $vehicleInfo = $vehicle ? ($vehicle->vehicle_brand . ' ' . $vehicle->vehicle_model . ' (' . $vehicle->plate_number . ')') : 'N/A';

            \App\Models\AdminNotification::create([
                'type' => 'new_booking',
                'notifiable_type' => 'admin',
                'notifiable_id' => null, // Null often implies 'all admins' or handled by scope
                'user_id' => $customer->userID ?? Auth::id(),
                'booking_id' => $booking->bookingID,
                'payment_id' => null,
                'message' => "New Booking Request: #{$booking->bookingID} - {$vehicleInfo}",
                'data' => [
                    'booking_id' => $booking->bookingID,
                    'vehicle_info' => $vehicleInfo,
                    'customer_name' => $customer->user->name ?? 'Customer',
                    'amount' => $booking->rental_amount
                ],
                'is_read' => false,
            ]);
        } catch (\Exception $e) {
            // Log the error but don't fail the booking entirely
            Log::warning('Failed to create new booking notification: ' . $e->getMessage());
        }

        // 2. Wallet Logic (Optional)
        // Since payment happens in the next step (payments.create),
        // you likely don't need to deduct funds here yet.
        // You can leave this empty or add specific logic if needed.
    }
    /**
     * Process the booking extension/reschedule request.
     */
    public function processExtend(Request $request, $id)
    {
        $booking = Booking::with('vehicle')->findOrFail($id);

        // Security check
        if ($booking->customer->userID !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        // 1. Validate New Dates
        $request->validate([
            'start_date' => 'required|date|after:now',
            'end_date'   => 'required|date|after:start_date',
        ]);

        $newStart = Carbon::parse($request->start_date);
        $newEnd   = Carbon::parse($request->end_date);

        // 2. Check Availability (Exclude current booking ID from check)
        $isConflict = Booking::where('vehicleID', $booking->vehicleID)
            ->where('bookingID', '!=', $booking->bookingID) 
            ->where('booking_status', '!=', 'Cancelled')
            ->where(function ($query) use ($newStart, $newEnd) {
                $query->whereBetween('rental_start_date', [$newStart, $newEnd])
                      ->orWhereBetween('rental_end_date', [$newStart, $newEnd])
                      ->orWhere(function ($q) use ($newStart, $newEnd) {
                          $q->where('rental_start_date', '<', $newStart)
                            ->where('rental_end_date', '>', $newEnd);
                      });
            })
            ->exists();

        if ($isConflict) {
            return back()->with('error', 'The vehicle is unavailable for these new dates. Please choose another range.');
        }

        // 3. Recalculate Price
        // Calculate new duration
        $newDuration = $newStart->diffInDays($newEnd) + 1;
        
        // Get base rental price
        $vehiclePrice = $booking->vehicle->rental_price;
        
        // Recalculate Add-ons cost (if you want to keep addons)
        $addonsCost = 0;
        if ($booking->addOns_item) {
            $addons = explode(',', $booking->addOns_item);
            $addonPrices = ['power_bank' => 5, 'phone_holder' => 5, 'usb_wire' => 3];
            foreach ($addons as $addon) {
                $addonsCost += ($addonPrices[$addon] ?? 0);
            }
        }

        // Calculate New Total
        // Note: We keep the original deposit/surcharge logic or recalculate it
        $newRentalAmount = ($vehiclePrice + $addonsCost) * $newDuration + ($booking->pickup_surcharge ?? 0) + 50; // +50 Deposit

        // 4. Update Booking
        $booking->update([
            'rental_start_date' => $newStart,
            'rental_end_date'   => $newEnd,
            'duration'          => $newDuration,
            'rental_amount'     => $newRentalAmount,
            'lastUpdateDate'    => now()
        ]);

        return redirect()->route('bookings.show', $id)
            ->with('success', 'Booking rescheduled successfully! Your deposit is safe.');
    }
}
