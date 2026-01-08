<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Customer;
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
            $status = 'Completed';
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

   public function store(Request $request, $vehicleID)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            $request->session()->put('url.intended', url()->previous());
            return redirect()->route('login')->with('error', 'Please sign in to proceed with booking.');
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
                return redirect('/')->with('error', 'Session expired. Please start your booking again.');
            }

            $vehicle = Vehicle::findOrFail($bookingData['vehicleID']);
            $user = Auth::user();

            // Parse add-ons
            $addonsArray = !empty($bookingData['addOns_item']) ? explode(',', $bookingData['addOns_item']) : [];
            $addonDetails = [];
            
            // Addon configuration
            $addonPrices = ['power_bank' => 5, 'phone_holder' => 5, 'usb_wire' => 3];
            $addonNames = ['power_bank' => 'Power Bank', 'phone_holder' => 'Phone Holder', 'usb_wire' => 'USB Wire'];

            foreach ($addonsArray as $addon) {
                if (isset($addonPrices[$addon])) {
                    $addonDetails[] = [
                        'name'  => $addonNames[$addon],
                        'price' => $addonPrices[$addon],
                        'total' => $addonPrices[$addon] * $bookingData['duration']
                    ];
                }
            }

            // Standardize booking data keys for the view
            $standardizedBookingData = [
                'rental_start_date' => $bookingData['rental_start_date'],
                'rental_end_date' => $bookingData['rental_end_date'],
                'duration' => $bookingData['duration'],
                'pickup_point' => $bookingData['pickup_point'],
                'return_point' => $bookingData['return_point'],
                'total_amount' => $bookingData['rental_amount'], // Map rental_amount to total_amount
                'vehicleID' => $bookingData['vehicleID'],
            ];

            $tempBooking = new Booking([
                'duration' => $bookingData['duration'],
                'rental_amount' => $bookingData['rental_amount'],
            ]);
            
            $depositAmount = $this->paymentService->calculateDeposit($tempBooking);

            // Handle potential missing wallet gracefully
            $customer = $user->customer;
            $walletAccount = $customer ? $customer->walletAccount : null;
            $walletBalance = $walletAccount ? $walletAccount->wallet_balance : 0;
            $canSkipDeposit = $this->paymentService->canSkipDepositWithWallet($user->userID, $depositAmount);

            return view('bookings.confirm', [
                'bookingData' => $standardizedBookingData,
                'vehicle'     => $vehicle,
                'addons'      => $addonDetails,
                'depositAmount' => $depositAmount,
                'walletBalance' => $walletBalance,
                'canSkipDeposit' => $canSkipDeposit,
            ]);

        } catch (\Exception $e) {
            Log::error('Confirmation Page Error: ' . $e->getMessage());
            return redirect('/')->with('error', 'Error loading confirmation page. Please try again.');
        }
    }

    public function finalize(Request $request)
    {
        // Direct file debug - this will always work
        $debugLog = storage_path('logs/finalize_debug.txt');
        file_put_contents($debugLog, date('Y-m-d H:i:s') . " - Finalize started\n", FILE_APPEND);
        file_put_contents($debugLog, "Request data: " . json_encode($request->all()) . "\n", FILE_APPEND);
        
        // Log incoming request for debugging
        Log::info('Finalize booking called', [
            'method' => $request->method(),
            'has_vehicle_id' => $request->has('vehicle_id'),
            'vehicle_id' => $request->input('vehicle_id'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ]);
        
        // 1. Validation
        try {
            $request->validate([
                'vehicle_id' => 'required|integer',
                'start_date' => 'required|date',
'start_time' => [
    'required',
    'date_format:H:i',
    function ($attr, $value, $fail) {
        if (!in_array(substr($value, 3, 2), ['00', '30'])) {
            $fail('Time must be on the hour or half-hour (00 or 30).');
        }
    }
],                'end_date'   => 'required|date|after_or_equal:start_date',
                'end_time'   => [
                    'required',
                    'date_format:H:i',
                    function ($attr, $value, $fail) {
                        if (!in_array(substr($value, 3, 2), ['00', '30'])) {
                            $fail('Time must be on the hour or half-hour (00 or 30).');
                        }
                    }
                ],
                'pickup_point' => 'required|string',
                'return_point' => 'required|string',
                'total_amount' => 'required|numeric',
            ]);
            file_put_contents($debugLog, "Validation passed\n", FILE_APPEND);
        } catch (\Illuminate\Validation\ValidationException $e) {
            file_put_contents($debugLog, "Validation failed: " . json_encode($e->errors()) . "\n", FILE_APPEND);
            throw $e;
        }

        // Wrap the entire transaction in a Try-Catch block
        try {
            DB::beginTransaction(); // Start Transaction (Safety Net)
            file_put_contents($debugLog, "Transaction started\n", FILE_APPEND);

            // 2. Get Customer
            $userID = Auth::user()->userID;
            file_put_contents($debugLog, "Looking for customer with userID: $userID\n", FILE_APPEND);
            
            $customer = Customer::where('userID', $userID)->first();
            file_put_contents($debugLog, "Customer found: " . ($customer ? "Yes (ID: {$customer->customerID})" : "No") . "\n", FILE_APPEND);

            if (!$customer) {
                file_put_contents($debugLog, "ERROR: Customer not found!\n", FILE_APPEND);
                throw new \Exception('Customer profile not found for this user.');
            }

            // 3. Create Booking
            $duration = Carbon::parse($request->start_date)
                ->diffInDays(Carbon::parse($request->end_date)) + 1;
            
            // Get addons from request or session
            $addons = $request->input('addons', []);
            $addonsArray = [];
            if (!empty($addons) && is_array($addons)) {
                foreach ($addons as $addon) {
                    if (isset($addon['name'])) {
                        $addonName = strtolower(str_replace(' ', '_', $addon['name']));
                        if (str_contains($addonName, 'gps')) {
                            $addonsArray[] = 'gps';
                        } elseif (str_contains($addonName, 'child')) {
                            $addonsArray[] = 'child_seat';
                        } elseif (str_contains($addonName, 'insurance')) {
                            $addonsArray[] = 'insurance';
                        }
                    }
                }
            }
            
            // Get addons from session if not in request
            $bookingData = session('booking_data', []);
            if (empty($addonsArray) && !empty($bookingData['addOns_item'])) {
                $addonsArray = explode(',', $bookingData['addOns_item']);
            }
            
            file_put_contents($debugLog, "Creating booking...\n", FILE_APPEND);
            
            $booking = new Booking();
            $booking->customerID = $customer->customerID;
            $booking->vehicleID  = $request->vehicle_id;
            $booking->rental_start_date = $request->start_date . ' ' . $request->start_time;
            $booking->rental_end_date   = $request->end_date . ' ' . $request->end_time;
            $booking->duration = $duration;
            $booking->pickup_point = $request->pickup_point;
            $booking->return_point = $request->return_point;
            $booking->rental_amount = $request->total_amount;
            $booking->addOns_item = !empty($addonsArray) ? implode(',', $addonsArray) : null;
            $booking->booking_status = 'Pending';
            $booking->lastUpdateDate = now();
            
            file_put_contents($debugLog, "Booking data: " . json_encode($booking->toArray()) . "\n", FILE_APPEND);
            
            try {
                $saved = $booking->save();
                file_put_contents($debugLog, "Booking saved: " . ($saved ? "Yes (ID: {$booking->bookingID})" : "No") . "\n", FILE_APPEND);
                if (!$saved) {
                    throw new \Exception('Failed to save booking record.');
                }
            } catch (\Exception $saveError) {
                file_put_contents($debugLog, "Booking save error: " . $saveError->getMessage() . "\n", FILE_APPEND);
                throw $saveError;
            }
            
            // Clear booking session data after successful save
            session()->forget('booking_data');

            // 4. Update Wallet (Robust Logic)
            file_put_contents($debugLog, "Updating wallet...\n", FILE_APPEND);
            $wallet = \App\Models\WalletAccount::where('customerID', $customer->customerID)->first();

            if ($wallet) {
                // Update existing wallet
                $wallet->outstanding_amount = ($wallet->outstanding_amount ?? 0) + $booking->rental_amount;
                $wallet->wallet_lastUpdate_Date_Time = now();
                $wallet->save();
                file_put_contents($debugLog, "Wallet updated\n", FILE_APPEND);
            } else {
                // Create new wallet
                \App\Models\WalletAccount::create([
                    'customerID'         => $customer->customerID,
                    'wallet_balance'     => 0.00,
                    'outstanding_amount' => $booking->rental_amount,
                    'wallet_status'      => 'Active',
                    'wallet_lastUpdate_Date_Time' => now()
                ]);
                file_put_contents($debugLog, "New wallet created\n", FILE_APPEND);
            }

            DB::commit(); // Save everything if successful
            file_put_contents($debugLog, "Transaction committed successfully!\n", FILE_APPEND);

            // 5. Success Redirect
            $redirectUrl = route('payments.create', ['booking' => $booking->bookingID]);
            file_put_contents($debugLog, "Redirecting to: $redirectUrl\n", FILE_APPEND);
            
            return redirect()->route('payments.create', ['booking' => $booking->bookingID])
                             ->with('success', 'Booking submitted successfully! Please proceed to payment.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            file_put_contents($debugLog, "VALIDATION ERROR: " . json_encode($e->errors()) . "\n", FILE_APPEND);
            Log::error('Finalize Booking Validation Error: ' . $e->getMessage());
            return redirect()->route('booking.confirm')
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack(); // Undo everything if error occurs
            file_put_contents($debugLog, "EXCEPTION: " . $e->getMessage() . "\n", FILE_APPEND);
            file_put_contents($debugLog, "Stack trace: " . $e->getTraceAsString() . "\n", FILE_APPEND);
            Log::error('Finalize Booking Error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // Redirect to confirm page with error, not back (which might cause loop)
            return redirect()->route('booking.confirm')
                ->with('error', 'System Error: Unable to complete booking. Please try again or contact support.');
        }
    }

    /**
     * Download the invoice PDF for a specific booking.
     */
    public function downloadInvoice($id)
    {
        // 1. Find the booking with all necessary relationships
        $booking = \App\Models\Booking::with(['invoice', 'payments', 'vehicle', 'customer'])
            ->findOrFail($id);

        // 2. Security Check: Ensure this booking belongs to the logged-in user
        // We compare the booking's customer userID with the logged-in Auth ID
        if ($booking->customer->userID !== \Illuminate\Support\Facades\Auth::id()) {
            abort(403, 'Unauthorized action. This invoice does not belong to you.');
        }

        // 3. Check if invoice exists
        $invoiceData = $booking->invoice;
        if (!$invoiceData) {
            return redirect()->back()->with('error', 'Invoice has not been generated yet.');
        }

        // 4. Calculate Totals
        $rentalAmount  = $booking->rental_amount;
        $depositAmount = $booking->deposit_amount;
        $totalPaid     = $booking->payments->where('payment_status', 'Verified')->sum('total_amount');

        // 5. Generate PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice', compact('booking', 'invoiceData', 'rentalAmount', 'depositAmount', 'totalPaid'));

        // 6. Download
        return $pdf->download('Invoice-' . $booking->bookingID . '.pdf');
    }
}