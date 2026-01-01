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
use Illuminate\Support\Facades\DB; // Added DB Facade import

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

        session(['booking_data' => $bookingData]);

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
        // 1. Validation
        $validated = $request->validate([
            'vehicle_id' => 'required|integer',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'pickup_point' => 'required|string',
            'return_point' => 'required|string',
            'total_amount' => 'required|numeric',
        ]);

        // 2. Get Customer
        $customer = Customer::where('user_id', Auth::id())->first();

        if (!$customer) {
            return redirect()->back()->withErrors(['msg' => 'Error: Customer profile not found.']);
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
        $booking->booking_status = 'pending';
        $booking->creationDate   = now();
        $booking->save();

        // ---------------------------------------------------------
        // 4. UPDATE WALLET DEBT (Robust "Lazy Creation" Version)
        // ---------------------------------------------------------
        // This handles both EXISTING users (updates debt) and 
        // USERS WITH NO WALLET (creates wallet with debt).
        
        $wallet = DB::table('walletaccount')
                    ->where('customerID', $customer->customerID)
                    ->first();

        if ($wallet) {
            // SCENARIO A: Wallet Exists -> Update it
            DB::table('walletaccount')
                ->where('walletAccountID', $wallet->walletAccountID)
                ->update([
                    'outstanding_amount' => $wallet->outstanding_amount + $booking->total_amount,
                    'last_update_datetime' => now()
                ]);
        } else {
            // SCENARIO B: Wallet Missing -> Create it!
            DB::table('walletaccount')->insert([
                'customerID'         => $customer->customerID,
                'user_id'            => Auth::id(),
                'available_balance'  => 0.00,
                'outstanding_amount' => $booking->total_amount, // The debt starts here
                'wallet_status'      => 'Active',
                'last_update_datetime' => now()
            ]);
        }
        // ---------------------------------------------------------

        // 5. Redirect to Payment
        return redirect()->route('payments.create', ['booking' => $booking->bookingID])
                         ->with('success', 'Booking submitted! Outstanding balance updated.');
    }

    public function downloadInvoice($id)
    {
        // 1. Find the booking with its relationships
        $booking = \App\Models\Booking::where('bookingID', $id)
                          ->with(['vehicle', 'customer', 'payments'])
                          ->firstOrFail();

        // 2. Security Check
        if (!$booking->customer || $booking->customer->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this invoice.');
        }

        // 3. Check if payment is verified
        $verifiedPayment = $booking->payments->where('status', 'Verified')->first();
        if (!$verifiedPayment) {
            abort(403, 'Invoice not available until payment is verified.');
        }

        // 4. Generate PDF
        $pdf = Pdf::loadView('pdf.invoice', compact('booking'));
        return $pdf->download('Invoice-'.$booking->bookingID.'.pdf');
    }
}