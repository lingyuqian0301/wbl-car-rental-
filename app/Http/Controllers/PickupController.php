<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;

class PickupController extends Controller
{
    /**
     * Show the pickup confirmation form
     */
    public function show(Booking $booking)
    {
        // Verify the booking belongs to the authenticated customer
        if ($booking->customer->userID !== auth()->id()) {
            abort(403, 'Unauthorized access');
        }

        // Get related data
        $customer = $booking->customer;
        $vehicle = $booking->vehicle;

        return view('bookings.pickup', compact('booking', 'customer', 'vehicle'));
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

        // Redirect to return step
        return redirect()->route('return.show', $booking)
            ->with('success', 'Vehicle pickup confirmed. Please proceed to vehicle return.');
    }
}
