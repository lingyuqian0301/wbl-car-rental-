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
use Illuminate\Support\Facades\Log;

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
                return view('bookings.index', ['bookings' => collect([])]);
            }

            $bookings = \App\Models\Booking::where('customerID', $customer->customerID)
                        ->with(['vehicle', 'payments'])
                        ->orderBy('bookingID', 'desc')
                        ->paginate(10);

            // Determine overall status for the dashboard
            $status = 'Completed';
            if ($bookings->isNotEmpty()) {
                $firstBooking = $bookings->first();
                // FIX: Use 'payment_status' instead of 'status'
                if ($firstBooking->payments->where('payment_status', 'Pending')->isNotEmpty() ||
                    $firstBooking->payments->where('payment_status', 'Rejected')->isNotEmpty()) {
                    $status = 'Pending';
                // FIX: Use 'booking_status' instead of 'status'
                } elseif ($firstBooking->booking_status === 'Confirmed') {
                    $status = 'Confirmed';
                } elseif ($firstBooking->booking_status === 'Cancelled') {
                    $status = 'Cancelled';
                }
            }

            return view('bookings.index', [
                'bookings' => $bookings,
                'status' => $status,
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching bookings: ' . $e->getMessage());
            return redirect()->route('home')->with('error', 'Unable to load bookings.');
        }
    }

    /**
     * Display the specified booking.
     */
    public function show(Booking $booking): View
    {
        $isAuthorized = false;

        // 1. Check if the booking belongs to the logged-in user
        if ($booking->customer && $booking->customer->userID === Auth::user()->userID) {
            $isAuthorized = true;
        }

        // 2. Allow Admins/Staff
        if (Auth::user()->isAdmin() || Auth::user()->isStaff()) {
            $isAuthorized = true;
        }

        if (!$isAuthorized) {
            abort(403, 'You are not authorized to view this booking.');
        }

        try {
            $booking->load(['vehicle', 'payments']);

            // FIX: Use 'payment_status'
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
        if (!Auth::check()) {
            $request->session()->put('url.intended', url()->previous());
            return redirect()->route('login')->with('error', 'Please sign in to proceed with booking.');
        }

        $request->validate([
            'start_date'    => 'required|date|after_or_equal:today',
            'end_date'      => 'required|date|after:start_date',
            'pickup_point'  => 'required|string|max:100',
            'return_point'  => 'required|string|max:100',
        ]);

        if (!$request->filled('start_date') || !$request->filled('end_date')) {
            return back()->withErrors(['error' => 'Start date and end date are required.']);
        }

        // 1. Check Conflicts (Using Correct Column Names)
        $conflictingBooking = Booking::where('vehicleID', $vehicleID)
            ->where('booking_status', '!=', 'Cancelled')
            ->where(function ($q) use ($request) {
                // Assuming DB columns are rental_start_date/rental_end_date
                $q->where('rental_start_date', '<=', $request->end_date)
                  ->where('rental_end_date', '>=', $request->start_date);
            })
            ->first();

        if ($conflictingBooking) {
            $currentCustomer = Customer::where('userID', Auth::user()->userID)->first();
            if ($currentCustomer && $conflictingBooking->customerID == $currentCustomer->customerID) {
                return redirect()->route('bookings.index')
                    ->with('error', 'You already have a PENDING booking for these dates!');
            } else {
                return back()->withErrors('Vehicle is unavailable for these dates.');
            }
        }

        // 2. Calculate Costs
        $duration = Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date)) + 1;
        $vehicle = Vehicle::findOrFail($vehicleID);
        $vehiclePrice = $vehicle->rental_price; // or price_per_day depending on your DB

        // Add-ons
        $addons = $request->addons ?? [];
        $addonPrices = ['gps' => 10, 'child_seat' => 15, 'insurance' => 30];
        $addonsPerDay = 0;
        foreach ($addons as $addon) {
            $addonsPerDay += $addonPrices[$addon] ?? 0;
        }

        $totalAmount = ($vehiclePrice + $addonsPerDay) * $duration;

        // 3. Prepare Session Data
        // FIX: CHANGED KEYS to 'start_date' and 'end_date' so confirm() can find them!
        $bookingData = [
            'vehicleID'      => $vehicleID,
            'start_date'     => $request->start_date, // Fixed Key
            'end_date'       => $request->end_date,   // Fixed Key
            'pickup_point'   => $request->pickup_point,
            'return_point'   => $request->return_point,
            'duration'       => $duration,
            'addOns_item'    => implode(',', $addons),
            'rental_amount'  => $totalAmount,
            'booking_status' => 'Pending',
        ];

        session(['booking_data' => $bookingData]);

        return redirect()->route('booking.confirm');
    }

    public function confirm()
    {
        try {
            $bookingData = session('booking_data');

            // 1. Safety Check (Now passes because keys match)
            if (!$bookingData || !isset($bookingData['vehicleID']) || !isset($bookingData['start_date'])) {
                return redirect()->route('home')->with('error', 'Session expired. Please select your vehicle again.');
            }

            $vehicle = Vehicle::findOrFail($bookingData['vehicleID']);
            $user = Auth::user();

            // 2. Add-ons
            $addonsArray = !empty($bookingData['addOns_item']) ? explode(',', $bookingData['addOns_item']) : [];
            $addonDetails = [];
            $addonPrices = ['gps' => 10, 'child_seat' => 15, 'insurance' => 30];
            $addonNames = ['gps' => 'GPS Navigation', 'child_seat' => 'Child Seat', 'insurance' => 'Full Insurance'];

            foreach ($addonsArray as $addon) {
                if (isset($addonPrices[$addon])) {
                    $addonDetails[] = [
                        'name'  => $addonNames[$addon],
                        'price' => $addonPrices[$addon],
                        'total' => $addonPrices[$addon] * ($bookingData['duration'] ?? 1)
                    ];
                }
            }

            // 3. Deposit Calculation
            $tempBooking = new Booking([
                'duration' => $bookingData['duration'] ?? 1,
                'rental_amount' => $bookingData['rental_amount'] ?? 0,
            ]);
            $depositAmount = $this->paymentService->calculateDeposit($tempBooking);

            // 4. Wallet Check (Using 'outstanding_amount')
            $customer = $user->customer;
            $walletAccount = $customer ? $customer->walletAccount : null;

            // Note: If you want to show Available Balance, logic might be different depending on your app rules.
            // Assuming we just pass the object or 0.
            $walletBalance = 0; // Or fetch 'wallet_balance' if you added it back, or just use 0 to be safe.

            $canSkipDeposit = $this->paymentService->canSkipDepositWithWallet($user->userID, $depositAmount);

            return view('bookings.confirm', [
                'bookingData'    => $bookingData,
                'vehicle'        => $vehicle,
                'addons'         => $addonDetails,
                'depositAmount'  => $depositAmount,
                'walletBalance'  => $walletBalance,
                'canSkipDeposit' => $canSkipDeposit,
            ]);

        } catch (\Exception $e) {
            Log::error('Confirmation Page Error: ' . $e->getMessage());
            return redirect()->route('home')->with('error', 'Error loading confirmation page.');
        }
    }

    public function finalize(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|integer',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'pickup_point' => 'required|string',
            'return_point' => 'required|string',
            'total_amount' => 'required|numeric',
        ]);

        try {
            DB::beginTransaction();

            $customer = Customer::where('userID', Auth::user()->userID)->first();
            if (!$customer) {
                throw new \Exception('Customer profile not found.');
            }

            $duration = Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date)) + 1;

            $booking = new Booking();
            $booking->customerID = $customer->customerID;
            $booking->vehicleID  = $request->vehicle_id;
            $booking->rental_start_date = $request->start_date;
            $booking->rental_end_date   = $request->end_date;
            $booking->duration = $duration;
            $booking->pickup_point = $request->pickup_point;
            $booking->return_point = $request->return_point;
            $booking->rental_amount = $request->total_amount;
            // FIX: Using 'total_amount' if that is the column name in DB, otherwise use 'rental_amount'
            // Your DB has 'rental_amount' on Booking table and 'total_amount' on Payment table.
            // Keeping 'rental_amount' here as per your Booking Model.

            $booking->booking_status = 'Pending';
            $booking->lastUpdateDate = now();

            if (!$booking->save()) {
                throw new \Exception('Failed to save booking record.');
            }

            // Update Wallet Outstanding
            $wallet = \App\Models\WalletAccount::where('customerID', $customer->customerID)->first();
            if ($wallet) {
                $wallet->outstanding_amount = ($wallet->outstanding_amount ?? 0) + $booking->rental_amount;
                $wallet->save(); // removed wallet_lastUpdate_Date_Time if column missing, else keep it
            } else {
                \App\Models\WalletAccount::create([
                    'customerID'         => $customer->customerID,
                    'outstanding_amount' => $booking->rental_amount,
                    // removed wallet_balance, wallet_status if they don't exist in new DB
                ]);
            }

            DB::commit();

            return redirect()->route('payments.create', ['booking' => $booking->bookingID])
                             ->with('success', 'Booking submitted! Please make payment.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Finalize Booking Error: ' . $e->getMessage());
            return back()->with('error', 'System Error: Unable to complete booking.');
        }
    }

    public function downloadInvoice($id)
    {
        try {
            $booking = Booking::where('bookingID', $id)
                            ->with(['vehicle', 'customer', 'payments'])
                            ->firstOrFail();

            if (!$booking->customer || $booking->customer->userID !== auth()->user()->userID) {
                abort(403);
            }

            // FIX: Use 'payment_status'
            $verifiedPayment = $booking->payments->where('payment_status', 'Verified')->first();
            if (!$verifiedPayment) {
                return back()->with('error', 'Invoice not available until payment is verified.');
            }

            $pdf = Pdf::loadView('pdf.invoice', compact('booking'));
            return $pdf->download('Invoice-'.$booking->bookingID.'.pdf');

        } catch (\Exception $e) {
            return back()->with('error', 'Invoice not found.');
        }
    }
}
