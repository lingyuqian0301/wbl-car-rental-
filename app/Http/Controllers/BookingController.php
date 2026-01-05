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
            $booking->load(['vehicle', 'payments.verifier']);

            $hasVerifiedPayment = $booking->payments()
                ->where('payment_status', 'Verified')
                ->exists();

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
            // Store the intended URL so we can redirect back after login
            $request->session()->put('url.intended', url()->previous());
            return redirect()->route('login')->with('error', 'Please sign in to proceed with booking.');
        }

        $request->validate([
            'start_date'    => 'required|date|after_or_equal:today',
            'end_date'      => 'required|date|after:start_date',
            'pickup_point'  => 'required|string|max:100',
            'return_point'  => 'required|string|max:100',
        ]);

        // Ensure 'start_date' and 'end_date' exist before proceeding
        if (!$request->filled('start_date') || !$request->filled('end_date')) {
            return back()->withErrors(['error' => 'Start date and end date are required.']);
        }

        // 1. Find the Conflicting Booking (if any)
        $conflictingBooking = Booking::where('vehicleID', $vehicleID)
            ->where('booking_status', '!=', 'Cancelled') // Ignore cancelled ones
            ->where(function ($q) use ($request) {
                $q->where('rental_start_date', '<=', $request->end_date)
                  ->where('rental_end_date', '>=', $request->start_date);
            })
            ->first(); // Get the actual record instead of just 'exists()'

        // 2. Analyze the Conflict
        if ($conflictingBooking) {
            
            // CHECK: Is it MY booking?
            $currentCustomer = Customer::where('userID', Auth::user()->userID)->first();
            
            if ($currentCustomer && $conflictingBooking->customerID == $currentCustomer->customerID) {
                // CASE A: You blocked yourself (Ghost Booking)
                return redirect()->route('bookings.index')
                    ->with('error', 'You already have a PENDING booking for these dates! Please pay for booking #' . $conflictingBooking->bookingID . ' or cancel it to make a new one.');
            } else {
                // CASE B: Someone else booked it
                return back()->withErrors('Vehicle is unavailable for these dates. It is currently being booked by another customer.');
            }
        }

        // --- If no conflict, proceed as normal ---

        // Duration (inclusive)
        $duration = Carbon::parse($request->start_date)
            ->diffInDays(Carbon::parse($request->end_date)) + 1;

        // Vehicle price
        $vehicle = Vehicle::findOrFail($vehicleID);
        $vehiclePrice = $vehicle->rental_price;

        // Add-ons
        $addons = $request->addons ?? [];
        $addonPrices = [
            'gps'        => 10,
            'child_seat' => 15,
            'insurance'  => 30,
        ];

        $addonsPerDay = 0;
        foreach ($addons as $addon) {
            $addonsPerDay += $addonPrices[$addon] ?? 0;
        }

        $addonsCharge = $addonsPerDay * $duration;
        $totalAmount  = ($vehiclePrice + $addonsPerDay) * $duration;

        // Prepare booking data
        $bookingData = [
            'vehicleID'      => $vehicleID,
            'rental_start_date' => $request->start_date,
            'rental_end_date'   => $request->end_date,
            'pickup_point'   => $request->pickup_point,
            'return_point'   => $request->return_point,
            'duration'       => $duration,
            'addOns_item'    => implode(',', $addons),
            'rental_amount'  => $totalAmount,
            'booking_status' => 'Pending',
        ];

        $start = $request->start_date;
$end   = $request->end_date;

        $exists = Booking::where('vehicleID', $vehicleID)
    ->where('booking_status', '!=', 'Cancelled')
    ->where(function ($q) use ($start, $end) {
        $q->where('rental_start_date', '<=', $end)
          ->where('rental_end_date', '>=', $start);
    })
    ->exists();

if ($exists) {
    return back()->withErrors(['date' => 'Selected dates are unavailable']);
}


        session(['booking_data' => $bookingData]);

        return redirect()->route('booking.confirm');
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

            $addonsArray = !empty($bookingData['addOns_item']) ? explode(',', $bookingData['addOns_item']) : [];
            $addonDetails = [];
            
            // Hardcoded prices (Best practice: Move to DB or Config later)
            $addonPrices = ['gps' => 10, 'child_seat' => 15, 'insurance' => 30];
            $addonNames = ['gps' => 'GPS Navigation', 'child_seat' => 'Child Seat', 'insurance' => 'Full Insurance Coverage'];

            foreach ($addonsArray as $addon) {
                if (isset($addonPrices[$addon])) {
                    $addonDetails[] = [
                        'name'  => $addonNames[$addon],
                        'price' => $addonPrices[$addon],
                        'total' => $addonPrices[$addon] * $bookingData['duration']
                    ];
                }
            }

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
                'bookingData' => $bookingData,
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
                'end_date'   => 'required|date|after_or_equal:start_date',
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
            $booking->rental_start_date = $request->start_date;
            $booking->rental_end_date   = $request->end_date;
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

    public function downloadInvoice($id)
    {
        try {
            $booking = \App\Models\Booking::where('bookingID', $id)
                              ->with(['vehicle', 'customer', 'payments'])
                              ->firstOrFail();

            if (!$booking->customer || $booking->customer->userID !== auth()->user()->userID) {
                abort(403, 'Unauthorized access to this invoice.');
            }

            $verifiedPayment = $booking->payments->where('payment_status', 'Verified')->first();
            if (!$verifiedPayment) {
                // User Friendly: Redirect back with message instead of crashing
                return back()->with('error', 'Invoice is not available until your payment is verified by Admin.');
            }

            $pdf = Pdf::loadView('pdf.invoice', compact('booking'));
            return $pdf->download('Invoice-'.$booking->bookingID.'.pdf');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return back()->with('error', 'Invoice not found.');
        } catch (\Exception $e) {
            Log::error('Invoice Download Error: ' . $e->getMessage());
            return back()->with('error', 'Unable to generate invoice at this time.');
        }
    }
}