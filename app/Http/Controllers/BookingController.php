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
                $activeVoucher = DB::table('voucher')
                    ->join('loyaltycard', 'voucher.loyaltyCardID', '=', 'loyaltycard.loyaltyCardID')
                    ->where('loyaltycard.customerID', $customer->customerID)
                    ->where('voucher.voucher_isActive', 1)
                    ->select('voucher.*')
                    ->first();
            }

            // Recalculate Logic
            $rentalPrice = $vehicle->rental_price * $bookingData['duration'];
            
            // Apply Discount (Free Rental)
            if ($activeVoucher) {
                $discountAmount = $rentalPrice; 
            }

            // Re-calculate Total
            // Note: Total in session usually includes rental + addons + surcharge + deposit
            // We need to subtract the discount from the ORIGINAL total calculated in store()
            $finalTotal = max(0, $bookingData['rental_amount'] - $discountAmount);

            // Update session for finalize step
            $bookingData['final_total'] = $finalTotal; // Store separately to avoid confusion
            $bookingData['discount_amount'] = $discountAmount;
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
                'discountAmount' => $discountAmount
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

            // 2. USE SESSION DATA FOR TOTAL (Safer than request input)
            $bookingData = session('booking_data');
            $finalRentalAmount = $bookingData['final_total'] ?? $request->total_amount; 

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
            $booking->booking_status = 'Pending';
            $booking->lastUpdateDate = now();
            
            $booking->save();

            // 4. DEACTIVATE VOUCHER
            if ($activeVoucher && isset($bookingData['discount_amount']) && $bookingData['discount_amount'] > 0) {
                DB::table('voucher')
                    ->where('voucherID', $activeVoucher->voucherID)
                    ->update(['voucher_isActive' => 0]); 
                
                Log::info("Voucher {$activeVoucher->voucher_code} applied to Booking #{$booking->bookingID}");
            }

            // 5. WALLET & NOTIFICATION
            $this->updateWalletAndNotify($customer, $booking);

            session()->forget('booking_data');
            DB::commit();

            return redirect()->route('payments.create', ['booking' => $booking->bookingID])
                ->with('success', 'Booking confirmed! Voucher applied.');

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
public function cancel(Request $request, $id)
    {
        // 1. Fetch booking with payments
        $booking = Booking::with(['customer', 'payments'])->findOrFail($id);

        // 2. Security: Ensure it belongs to the user
        if ($booking->customer->userID !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // 3. Logic: Allow cancel ONLY if status is NOT Confirmed AND Payment is NOT Verified
        
        // Check if Payment is Verified
        $hasVerifiedPayment = $booking->payments->contains('payment_status', 'Verified');

        if ($booking->booking_status === 'Confirmed') {
            return back()->with('error', 'Cannot cancel a booking that has already been Confirmed.');
        }

        if ($hasVerifiedPayment) {
            return back()->with('error', 'Cannot cancel a booking once payment has been Verified.');
        }

        // Also prevent cancelling if it's already cancelled or completed
        if (in_array($booking->booking_status, ['Cancelled', 'Completed'])) {
            return back()->with('error', 'Booking cannot be cancelled.');
        }

        // Proceed to Cancel
        $booking->booking_status = 'Cancelled';
        $booking->save();

        // Create Admin Notification for Cancellation
        try {
            $vehicle = $booking->vehicle;
            $vehicleInfo = $vehicle ? ($vehicle->vehicle_brand . ' ' . $vehicle->vehicle_model . ' (' . $vehicle->plate_number . ')') : 'N/A';
            
            \App\Models\AdminNotification::create([
                'type' => 'booking_cancelled',
                'notifiable_type' => 'admin',
                'notifiable_id' => null,
                'user_id' => Auth::id(),
                'booking_id' => $booking->bookingID,
                'payment_id' => null,
                'message' => "Booking cancelled: Booking #{$booking->bookingID} - {$vehicleInfo}",
                'data' => [
                    'booking_id' => $booking->bookingID,
                    'vehicle_info' => $vehicleInfo,
                    'customer_id' => $booking->customer->customerID ?? null,
                ],
                'is_read' => false,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Notification Error (Ignored): ' . $e->getMessage());
        }

        return back()->with('success', 'Booking cancelled successfully.');
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
}