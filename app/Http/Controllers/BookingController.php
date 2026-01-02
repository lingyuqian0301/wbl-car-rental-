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
            $customer = \App\Models\Customer::where('user_id', auth()->id())->first();

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
        if ($booking->customer && $booking->customer->user_id === Auth::id()) {
            $isAuthorized = true;
        }

        // 2. Allow Admins to view any booking (Optional but recommended)
        if (Auth::user()->role === 'admin' || Auth::user()->role === 'staff') {
            $isAuthorized = true;
        }

    if (!$isAuthorized) {
        abort(403, 'You are not authorized to view this booking.');
    }

        try {
            $booking->load(['vehicle', 'payments.verifier']);

            $hasVerifiedPayment = $booking->payments()
                ->where('status', 'Verified')
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

        // 1. Find the Conflicting Booking (if any)
        $conflictingBooking = Booking::where('vehicleID', $vehicleID)
            ->where('booking_status', '!=', 'Cancelled') // Ignore cancelled ones
            ->where(function ($q) use ($request) {
                $q->where('start_date', '<=', $request->end_date)
                  ->where('end_date', '>=', $request->start_date);
            })
            ->first(); // Get the actual record instead of just 'exists()'

        // 2. Analyze the Conflict
        if ($conflictingBooking) {
            
            // CHECK: Is it MY booking?
            $currentCustomer = Customer::where('user_id', Auth::id())->first();
            
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
            'start_date'     => $request->start_date,
            'end_date'       => $request->end_date,
            'pickup_point'   => $request->pickup_point,
            'return_point'   => $request->return_point,
            'duration_days'  => $duration,
            'addOns_item'    => implode(',', $addons),
            'addOns_charge'  => $addonsCharge,
            'total_amount'   => $totalAmount,
            'booking_status' => 'Pending',
        ];

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
                        'total' => $addonPrices[$addon] * $bookingData['duration_days']
                    ];
                }
            }

            $tempBooking = new Booking([
                'duration_days' => $bookingData['duration_days'],
                'number_of_days' => $bookingData['duration_days'],
                'total_amount' => $bookingData['total_amount'],
            ]);
            
            $depositAmount = $this->paymentService->calculateDeposit($tempBooking);

            // Handle potential missing wallet gracefully
            $walletAccount = $user->walletAccount; 
            $walletBalance = $walletAccount ? $walletAccount->available_balance : 0;
            $canSkipDeposit = $this->paymentService->canSkipDepositWithWallet($user->id, $depositAmount);

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
        // 1. Validation
        $request->validate([
            'vehicle_id' => 'required|integer',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'pickup_point' => 'required|string',
            'return_point' => 'required|string',
            'total_amount' => 'required|numeric',
        ]);

        // Wrap the entire transaction in a Try-Catch block
        try {
            DB::beginTransaction(); // Start Transaction (Safety Net)

            // 2. Get Customer
            $customer = Customer::where('user_id', Auth::id())->first();

            if (!$customer) {
                throw new \Exception('Customer profile not found for this user.');
            }

            // 3. Create Booking
            $booking = new Booking();
            $booking->customerID = $customer->customerID;
            $booking->vehicleID  = $request->vehicle_id;
            $booking->start_date = $request->start_date;
            $booking->end_date   = $request->end_date;
            $booking->pickup_point = $request->pickup_point;
            $booking->return_point = $request->return_point;
            $booking->total_amount = $request->total_amount;
            $booking->booking_status = 'Pending';
            $booking->creationDate   = now();
            
            if (!$booking->save()) {
                throw new \Exception('Failed to save booking record.');
            }

            // 4. Update Wallet (Robust Logic)
            $wallet = DB::table('walletaccount')
                        ->where('customerID', $customer->customerID)
                        ->first();

            if ($wallet) {
                // Update existing wallet
                // Try-Catch inside here in case column is missing
                try {
                     DB::table('walletaccount')
                        ->where('walletAccountID', $wallet->walletAccountID)
                        ->update([
                            'outstanding_amount' => $wallet->outstanding_amount + $booking->total_amount,
                            'last_update_datetime' => now()
                        ]);
                } catch (\Illuminate\Database\QueryException $qe) {
                    // Specific catch for missing column "outstanding_amount"
                    Log::warning("Wallet Update Failed (Column Missing?): " . $qe->getMessage());
                    // We continue anyway so the Booking is not lost!
                }
            } else {
                // Create new wallet
                DB::table('walletaccount')->insert([
                    'customerID'         => $customer->customerID,
                    'user_id'            => Auth::id(),
                    'available_balance'  => 0.00,
                    'outstanding_amount' => $booking->total_amount,
                    'wallet_status'      => 'Active',
                    'last_update_datetime' => now()
                ]);
            }

            DB::commit(); // Save everything if successful

            // 5. Success Redirect
            return redirect()->route('payments.create', ['booking' => $booking->bookingID])
                             ->with('success', 'Booking submitted successfully! Please proceed to payment.');

        } catch (\Exception $e) {
            DB::rollBack(); // Undo everything if error occurs
            Log::error('Finalize Booking Error: ' . $e->getMessage());
            
            // Show a generic error to user, but keep technical details in Log
            return redirect()->back()->with('error', 'System Error: Unable to complete booking. Please try again or contact support.');
        }
    }

    public function downloadInvoice($id)
    {
        try {
            $booking = \App\Models\Booking::where('bookingID', $id)
                              ->with(['vehicle', 'customer', 'payments'])
                              ->firstOrFail();

            if (!$booking->customer || $booking->customer->user_id !== auth()->id()) {
                abort(403, 'Unauthorized access to this invoice.');
            }

            $verifiedPayment = $booking->payments->where('status', 'Verified')->first();
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