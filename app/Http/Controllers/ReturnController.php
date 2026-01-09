<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;

class ReturnController extends Controller
{
    /**
     * Show the return confirmation form
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

        return view('bookings.return', compact('booking', 'customer', 'vehicle'));
    }

    /**
     * Handle return confirmation
     */
    public function confirm(Request $request, Booking $booking)
    {
        // Verify the booking belongs to the authenticated customer
        if ($booking->customer->userID !== auth()->id()) {
            abort(403, 'Unauthorized access');
        }

        // Validate the confirmation checkbox
        $request->validate([
            'confirm_return' => 'required|accepted',
        ], [
            'confirm_return.required' => 'You must confirm the vehicle return',
            'confirm_return.accepted' => 'You must accept the confirmation',
        ]);

        // Redirect with completion message
        // FIXED: Changed route from 'booking.show' to 'bookings.show'
        return redirect()->route('bookings.show', $booking)
            ->with('success', 'Vehicle return confirmed! Your rental is now complete. Thank you for using HASTA Travel & Tours.');
    }
}