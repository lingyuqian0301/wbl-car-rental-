<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BookingController extends Controller
{
    /**
     * Display a listing of the user's bookings.
     */
    public function index(): View
    {
        $bookings = Booking::with(['vehicle', 'payments'])
            ->where('user_id', Auth::id())
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
}
