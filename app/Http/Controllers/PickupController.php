<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\VehicleConditionForm;
use App\Models\VehicleConditionImage;

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

        // Uncomment this in production to enforce time restriction
        // if (now()->lessThan($allowedTime)) {
        //     $hoursLeft = now()->diffInHours($allowedTime);
        //     return redirect()->route('bookings.index')
        //         ->with('error', "The Pickup Form is not available yet. It will open 12 hours before your trip (in approx {$hoursLeft} hours).");
        // }

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
        // 3. KEY IMAGE LOGIC
        // =========================================================
        $keyLocationImage = 'assets/dummy_key_location.jpg'; 

        // Get related data
        $customer = $booking->customer;
        $vehicle = $booking->vehicle;

        return view('bookings.pickup', compact('booking', 'customer', 'vehicle', 'keyLocationImage'));
    }

    /**
     * Handle pickup confirmation
     */
    public function confirm(Request $request, Booking $booking)
    {
        // A. Security Check
        if ($booking->customer->userID !== auth()->id()) {
            abort(403, 'Unauthorized access');
        }

        // B. Validation
        $validated = $request->validate([
            'confirm_pickup' => 'required|accepted',
            'mileage' => 'required|integer|min:0',
            'fuel_level' => 'required|integer|min:0|max:100',
            'date_check' => 'required|date',
            'remarks' => 'nullable|string',
            // Validate images
            'front_image' => 'nullable|image|max:5120',
            'back_image' => 'nullable|image|max:5120',
            'left_image' => 'nullable|image|max:5120',
            'right_image' => 'nullable|image|max:5120',
            'fuel_image' => 'nullable|image|max:5120',
            'additional_images.*' => 'nullable|image|max:5120',
        ]);

        // C. Fuel Mapping Logic
        $fuelVal = (int) $request->fuel_level;
        $fuelEnum = 'EMPTY';
        if ($fuelVal >= 88) $fuelEnum = 'FULL';
        elseif ($fuelVal >= 63) $fuelEnum = '3/4';
        elseif ($fuelVal >= 38) $fuelEnum = '1/2';
        elseif ($fuelVal >= 13) $fuelEnum = '1/4';

        // D. Create Database Record
        $form = VehicleConditionForm::create([
            'form_type' => 'RECEIVE',
            'odometer_reading' => $request->mileage,
            'fuel_level' => $fuelEnum,
            'scratches_notes' => $request->remarks,
            'reported_dated_time' => $request->date_check,
            'bookingID' => $booking->bookingID,
        ]);

        // E. Save Images to myportfolio public folder
        // Uploads are stored in: C:\xampp\htdocs\myportfolio\public\uploads\vehicle_conditions
        $imageFields = ['front_image', 'back_image', 'left_image', 'right_image', 'fuel_image'];

        foreach ($imageFields as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $fileName = time() . '_' . $field . '_' . $file->getClientOriginalName();
                
                // Upload to myportfolio public folder
                $path = $file->storeAs('uploads/vehicle_conditions', $fileName, 'wbl_public'); 

                VehicleConditionImage::create([
                    'image_path' => $path, 
                    'image_taken_time' => now(),
                    'formID' => $form->formID,
                ]);
            }
        }

        // Handle extra images
        if ($request->hasFile('additional_images')) {
            foreach ($request->file('additional_images') as $index => $file) {
                $fileName = time() . '_additional_' . $index . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('uploads/vehicle_conditions', $fileName, 'wbl_public');

                VehicleConditionImage::create([
                    'image_path' => $path,
                    'image_taken_time' => now(),
                    'formID' => $form->formID,
                ]);
            }
        }

        // F. UPDATE BOOKING STATUS TO ONGOING
        $booking->booking_status = 'Ongoing';
        $booking->save();

        return redirect()->route('bookings.show', $booking)
            ->with('success', 'Vehicle pickup confirmed. Your booking is now ongoing.');
    }
}
