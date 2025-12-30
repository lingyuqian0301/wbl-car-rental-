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
    public function index(): View
    {
        $bookings = Booking::with(['vehicle', 'payments'])
            ->where('customerID', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('bookings.index', [
            'bookings' => $bookings,
        ]);
    }

    /**
     * Display the specified booking.
     */
    public function show(Booking $booking): View
    {
        // Ensure the booking belongs to the authenticated user
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this booking.');
        }

        $booking->load(['vehicle', 'payments.verifier']);

        // Check if payment is verified
        $hasVerifiedPayment = $booking->payments()
            ->where('status', 'Verified')
            ->exists();

        return view('bookings.show', [
            'booking' => $booking,
            'hasVerifiedPayment' => $hasVerifiedPayment,
        ]);
    }
public function store(Request $request, $vehicleID)
{
    $request->validate([
        'start_date'    => 'required|date|after_or_equal:today',
        'end_date'      => 'required|date|after:start_date',
        'pickup_point'  => 'required|string|max:100',
        'return_point'  => 'required|string|max:100',
    ]);

    // Check overlapping booking
    $isBooked = Booking::where('vehicle_id', $vehicleID)
        ->where('status', '!=', 'Cancelled')
        ->where(function ($q) use ($request) {
            $q->where('start_date', '<=', $request->end_date)
              ->where('end_date', '>=', $request->start_date);
        })
        ->exists();

    if ($isBooked) {
        return back()->withErrors('Vehicle is already booked for the selected dates.');
    }

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

    // Prepare booking data for confirmation page
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
        'booking_status' => 'pending',
    ];

    // Store in session for confirmation page
    session(['booking_data' => $bookingData]);

    // Redirect to confirmation page
    return redirect()->route('booking.confirm');
}

    /**
     * Show booking confirmation page
     */
    public function confirm()
    {
        $bookingData = session('booking_data');

        if (!$bookingData) {
            return redirect('/')->with('error', 'No booking data found.');
        }

        $vehicle = Vehicle::findOrFail($bookingData['vehicleID']);
        $user = Auth::user();

        // Parse addons
        $addonsArray = !empty($bookingData['addOns_item']) ? explode(',', $bookingData['addOns_item']) : [];
        $addonDetails = [];

        $addonPrices = [
            'gps'        => 10,
            'child_seat' => 15,
            'insurance'  => 30,
        ];

        $addonNames = [
            'gps'        => 'GPS Navigation',
            'child_seat' => 'Child Seat',
            'insurance'  => 'Full Insurance Coverage',
        ];

        foreach ($addonsArray as $addon) {
            if (isset($addonPrices[$addon])) {
                $addonDetails[] = [
                    'name'  => $addonNames[$addon],
                    'price' => $addonPrices[$addon],
                    'total' => $addonPrices[$addon] * $bookingData['duration_days']
                ];
            }
        }

        // Calculate deposit amount
        $tempBooking = new Booking([
            'duration_days' => $bookingData['duration_days'],
            'total_price' => $bookingData['total_amount'],
        ]);
        $depositAmount = $this->paymentService->calculateDeposit($tempBooking);

        // Check wallet balance
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
    }

    /**
     * Finalize booking (called from confirmation page)
     */
    public function finalize(Request $request)
    {
        $bookingData = session('booking_data');

        if (!$bookingData) {
            return redirect('/')->with('error', 'Booking session expired.');
        }

        try {
            // Transform booking data to match database table structure
            $bookingToCreate = [
                'user_id'      => Auth::id(),
                'vehicle_id'   => $bookingData['vehicleID'],
                'start_date'   => $bookingData['start_date'],
                'end_date'     => $bookingData['end_date'],
                'duration_days' => $bookingData['duration_days'],
                'number_of_days' => $bookingData['duration_days'], // Add number_of_days for deposit calculation
                'total_price'  => $bookingData['total_amount'],
                'status'       => 'Pending',
                'keep_deposit' => $request->boolean('keep_deposit', false),
            ];

            // Log the booking data for debugging
            \Log::info('Creating booking with data:', $bookingToCreate);

            // Create the booking with transformed data
            $booking = Booking::create($bookingToCreate);

            // Log successful creation
            \Log::info('Booking created successfully with ID:', ['id' => $booking->id]);

            // Clear session
            session()->forget('booking_data');

            // Redirect to booking details with success message
            return redirect()->route('bookings.show', $booking->id)
                ->with('success', 'Booking confirmed successfully!');
        } catch (\Exception $e) {
            \Log::error('Error creating booking:', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->with('error', 'Error creating booking: ' . $e->getMessage());
        }
    }

}
