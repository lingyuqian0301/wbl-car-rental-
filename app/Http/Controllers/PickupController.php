<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PickupController extends Controller
{
    /**
     * Show the pickup confirmation form
     * Restrictions added:
     * 1. 12 Hours before rental start.
     * 2. Full Payment verified.
     */
    public function show(Booking $booking)
    {
        // 0. AUTH CHECK: Verify the booking belongs to the authenticated customer
        if ($booking->customer->userID !== auth()->id()) {
            abort(403, 'Unauthorized access');
        }

        // =========================================================
        // 1. TIME CHECK: 12 Hours Before Restriction
        // =========================================================
        $rentalStart = Carbon::parse($booking->rental_start_date);
        $allowedTime = $rentalStart->copy()->subHours(12);

        // If current time is BEFORE the allowed time (e.g., trying to access 2 days early)
        if (now()->lessThan($allowedTime)) {
            $hoursLeft = now()->diffInHours($allowedTime);
            return redirect()->route('bookings.index')
                ->with('error', "The Pickup Form is not available yet. It will open 12 hours before your trip (in approx {$hoursLeft} hours).");
        }

        // =========================================================
        // 2. PAYMENT CHECK: Full Payment Required
        // =========================================================
        $totalCost = $booking->total_amount ?? $booking->rental_amount;
        
        // Sum only VERIFIED payments
        $totalPaid = $booking->payments()
            ->where('payment_status', 'Verified')
            ->sum('total_amount');

        // Check if Paid Amount is less than Total Cost (allow RM 1 difference for rounding issues)
        if ($totalPaid < ($totalCost - 1)) {
            $balance = $totalCost - $totalPaid;
            // Redirect to Payment Page
            return redirect()->route('payments.create', ['booking' => $booking->bookingID])
                ->with('error', "You must complete Full Payment before picking up the car. Outstanding balance: RM " . number_format($balance, 2));
        }

        // =========================================================
        // 3. KEY IMAGE LOGIC (From Previous Customer)
        // =========================================================
        // Get related data
        $customer = $booking->customer;
        $vehicle = $booking->vehicle;

        // Find the last 'Return' form for THIS vehicle from PREVIOUS bookings
        $lastReturnForm = DB::table('booking_form')
            ->join('booking', 'booking.bookingID', '=', 'booking_form.bookingID')
            ->where('booking.vehicleID', $vehicle->vehicleID) // Same car
            ->where('booking_form.form_type', 'Return')       // Must be a return form
            ->where('booking.bookingID', '<', $booking->bookingID) // From a past booking
            ->orderBy('booking_form.submission_date', 'desc')
            ->first();

        // Use previous image or a default dummy image if it's the first time
        $keyLocationImage = $lastReturnForm ? $lastReturnForm->photo_key_location : 'assets/dummy_key_location.jpg';

        return view('bookings.pickup', compact('booking', 'customer', 'vehicle', 'keyLocationImage'));
    }

    /**
     * Handle pickup confirmation
     */
    public function confirm(Request $request, Booking $booking)
    {
        // Verify the booking belongs to the authenticated customer
        if ($booking->customer->userID !== auth()->id()) {
            abort(403, 'Unauthorized access');
        }

        // Validate the confirmation checkbox
        $request->validate([
            'confirm_pickup' => 'required|accepted',
        ], [
            'confirm_pickup.required' => 'You must confirm the vehicle receipt',
            'confirm_pickup.accepted' => 'You must accept the confirmation',
        ]);

        // Logic to save the actual form data (if you have input fields in bookings.pickup)
        // If your view has mileage/fuel inputs, save them to 'booking_form' table here using DB::table('booking_form')->insert(...)

        // Redirect to return step
        return redirect()->route('return.show', $booking)
            ->with('success', 'Vehicle pickup confirmed. Please proceed to vehicle return.');
    }
}