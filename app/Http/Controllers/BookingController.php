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
    $customer = \App\Models\Customer::where('user_id', auth()->id())->first();

    if (!$customer) {
        return view('bookings.index', ['bookings' => collect([])]);
    }

    $bookings = \App\Models\Booking::where('customerID', $customer->customerID)
   //IX: Load both 'vehicle' AND 'payments'
                    ->with(['vehicle', 'payments'])
                    ->orderBy('bookingID', 'desc')
                    ->paginate(10);

    return view('bookings.index', compact('bookings'));
}
    /**
     * Display the specified booking.
     */
    public function show(Booking $booking): View
    {
        // Ensure the booking belongs to the authenticated user
        // Check both customerID and user relationship
        $isAuthorized = false;
        if ($booking->customerID == Auth::id()) {
            $isAuthorized = true;
        }
        if ($booking->user && $booking->user->id === Auth::id()) {
            $isAuthorized = true;
        }
        if (!$isAuthorized) {
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
    // Use vehicleID and booking_status to match existing database structure
    $isBooked = Booking::where('vehicleID', $vehicleID)
        ->where('booking_status', '!=', 'Cancelled')
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
            'number_of_days' => $bookingData['duration_days'],
            'total_amount' => $bookingData['total_amount'],
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
    // 1. Validation (Matches the data seen in your screenshot)
    $validated = $request->validate([
        'vehicle_id' => 'required|integer',
        'start_date' => 'required|date',
        'end_date'   => 'required|date|after_or_equal:start_date',
        'pickup_point' => 'required|string',
        'return_point' => 'required|string',
        'total_amount' => 'required|numeric',
    ]);

    // 2. Get the logged-in Customer
    // (Assuming the user is logged in and linked to the customer table)
    $customer = Customer::where('user_id', Auth::id())->first();

    if (!$customer) {
        return redirect()->back()->withErrors(['msg' => 'Error: Customer profile not found.']);
    }

    // 3. Create the Booking
    $booking = new Booking();

    // Map the form inputs to your specific Database Columns
    $booking->customerID = $customer->customerID;
    $booking->vehicleID  = $request->vehicle_id;

    $booking->start_date = $request->start_date;
    $booking->end_date   = $request->end_date;
    $booking->pickup_point = $request->pickup_point;
    $booking->return_point = $request->return_point;
    $booking->total_amount = $request->total_amount;

    // Set Defaults
    $booking->booking_status = 'pending';
    $booking->creationDate   = now(); // Matches your SQL column 'creationDate'

    // 4. Save to Database
    $booking->save();

    // 5. Redirect to Payment (or Success Page)
    // We send the ID so the payment page knows which booking to pay for
    return redirect()->route('payments.create', ['booking' => $booking->bookingID])
                     ->with('success', 'Booking submitted! Please proceed to payment.');
}
public function downloadInvoice($id)
{
    // 1. Find the booking with its relationships
    // We remove the direct where('userID') because that column doesn't exist
    $booking = \App\Models\Booking::where('bookingID', $id)
                      ->with(['vehicle', 'customer', 'payments'])
                      ->firstOrFail();

    // 2. Security Check: Ensure this booking belongs to the logged-in user
    // We check the user_id inside the customer table
    if (!$booking->customer || $booking->customer->user_id !== auth()->id()) {
        abort(403, 'Unauthorized access to this invoice.');
    }

    // 3. Check if payment is verified
    $verifiedPayment = $booking->payments->where('status', 'Verified')->first();
    if (!$verifiedPayment) {
        abort(403, 'Invoice not available until payment is verified.');
    }

    // 4. Generate PDF
    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice', compact('booking'));
    return $pdf->download('Invoice-'.$booking->bookingID.'.pdf');
}
}
