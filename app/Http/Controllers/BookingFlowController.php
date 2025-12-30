<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Addon;
use Illuminate\Http\Request;

class BookingFlowController extends Controller
{
    // Show booking details + add-ons page
    public function details($carId)
    {
        $car = Vehicle::findOrFail($carId);
        // $addons = Addon::all();

        return view('booking.details', compact('car', 'addons'));
    }

    // Handle "Next" button
    public function proceed(Request $request, $carId)
    {
        // Store selected data in session (temporary)
        session([
            'booking.car_id' => $carId,
            'booking.addons' => $request->addons ?? [],
        ]);

        return redirect()->route('booking.payment');
    }
}
