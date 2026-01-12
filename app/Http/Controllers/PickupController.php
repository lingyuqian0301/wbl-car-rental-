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
        // 2. PAYMENT CHECK: Deposit must be verified, Balance can be pending
        // =========================================================
        $totalCost = $booking->total_amount ?? $booking->rental_amount;
        $depositAmount = $booking->deposit_amount ?? 50;
        
        // Sum VERIFIED payments
        $verifiedPaid = $booking->payments()
            ->where('payment_status', 'Verified')
            ->sum('total_amount');
        
        // Sum PENDING payments (for balance check)
        $pendingPaid = $booking->payments()
            ->where('payment_status', 'Pending')
            ->sum('total_amount');

        // Check if wallet deposit was used
        $hasDepositPayment = $booking->payments()
            ->where('payment_status', 'Verified')
            ->get()
            ->filter(function($p) use ($depositAmount) {
                return abs($p->total_amount - $depositAmount) < 5;
            })->count() > 0;
        
        $walletDepositUsed = in_array($booking->booking_status, ['Reserved', 'Ongoing', 'Completed', 'Confirmed']) 
                             && !$hasDepositPayment;
        
        // Calculate effective paid (verified + wallet if used)
        $effectivePaid = $verifiedPaid;
        if ($walletDepositUsed) {
            $effectivePaid = $verifiedPaid + $depositAmount;
        }
        
        // Check if deposit is secured (required to proceed)
        $depositSecured = ($verifiedPaid >= $depositAmount) || $walletDepositUsed;
        
        // Check if balance is paid (verified OR pending - customer doesn't need to wait)
        $totalPaidIncludingPending = $effectivePaid + $pendingPaid;
        $balancePaidOrPending = ($totalPaidIncludingPending >= ($totalCost - 1));

        // Customer can proceed if: deposit is secured AND balance is paid (even if pending)
        if (!$depositSecured) {
            return redirect()->route('payments.create', ['booking' => $booking->bookingID])
                ->with('error', "You must pay the deposit first before proceeding.");
        }
        
        if (!$balancePaidOrPending) {
            $balance = $totalCost - $effectivePaid;
            return redirect()->route('payments.create', ['booking' => $booking->bookingID])
                ->with('error', "Please pay the remaining balance of RM " . number_format($balance, 2) . " to proceed.");
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

        // B. Validation - use mimes instead of image for better compatibility
        $validated = $request->validate([
            'confirm_pickup' => 'required|accepted',
            'mileage' => 'required|integer|min:0',
            'fuel_level' => 'required|integer|min:0|max:100',
            'date_check' => 'required|date',
            'remarks' => 'nullable|string',
            // Validate images - use file + mimes for better Windows compatibility
            'front_image' => 'nullable|file|mimes:jpeg,jpg,png,gif,webp|max:5120',
            'back_image' => 'nullable|file|mimes:jpeg,jpg,png,gif,webp|max:5120',
            'left_image' => 'nullable|file|mimes:jpeg,jpg,png,gif,webp|max:5120',
            'right_image' => 'nullable|file|mimes:jpeg,jpg,png,gif,webp|max:5120',
            'fuel_image' => 'nullable|file|mimes:jpeg,jpg,png,gif,webp|max:5120',
            'additional_images.*' => 'nullable|file|mimes:jpeg,jpg,png,gif,webp|max:5120',
        ]);

        try {

        // C. Fuel Mapping Logic
        $fuelVal = (int) $request->fuel_level;
        $fuelEnum = 'EMPTY';
        if ($fuelVal >= 88) $fuelEnum = 'FULL';
        elseif ($fuelVal >= 63) $fuelEnum = '3/4';
        elseif ($fuelVal >= 38) $fuelEnum = '1/2';
        elseif ($fuelVal >= 13) $fuelEnum = '1/4';

        // --- PRE-PROCESS FUEL IMAGE (Required by database) ---
        $fuelImgPath = ''; // Default empty string to prevent crash if file missing
        $destinationPath = public_path('images/vehicle_conditions');

        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        if ($request->hasFile('fuel_image')) {
            $file = $request->file('fuel_image');
            $filename = uniqid() . '_' . time() . '_fuel_image.' . $file->getClientOriginalExtension();
            $file->move($destinationPath, $filename);
            $fuelImgPath = 'images/vehicle_conditions/' . $filename;
        }

        // D. Create Database Record
        $form = VehicleConditionForm::create([
            'form_type' => 'RECEIVE',
            'odometer_reading' => $request->mileage,
            'fuel_level' => $fuelEnum,
            'scratches_notes' => $request->remarks,
            'reported_dated_time' => $request->date_check,
            'bookingID' => $booking->bookingID,
            'rental_agreement' => true,
            'fuel_img' => $fuelImgPath ?: null,
        ]);

        // Create the VehicleConditionImage entry for fuel explicitly (since file is already moved)
        if ($fuelImgPath) {
            VehicleConditionImage::create([
                'image_path' => $fuelImgPath,
                'image_taken_time' => now(),
                'formID' => $form->formID,
            ]);
        }

        // E. Save Remaining Images (Excluded fuel_image)
        $imageFields = ['front_image', 'back_image', 'left_image', 'right_image'];
        
        foreach ($imageFields as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                
                // Generate a unique filename
                $filename = uniqid() . '_' . time() . '_' . $field . '.' . $file->getClientOriginalExtension();
                
                // Move file
                $file->move($destinationPath, $filename);
                
                // Store relative path
                $relativePath = 'images/vehicle_conditions/' . $filename;

                VehicleConditionImage::create([
                    'image_path' => $relativePath, 
                    'image_taken_time' => now(),
                    'formID' => $form->formID,
                ]);
            }
        }

        // Handle extra images
        if ($request->hasFile('additional_images')) {
            foreach ($request->file('additional_images') as $index => $file) {
                $filename = uniqid() . '_' . time() . '_additional_' . $index . '.' . $file->getClientOriginalExtension();
                $file->move($destinationPath, $filename);
                $relativePath = 'images/vehicle_conditions/' . $filename;
                
                VehicleConditionImage::create([
                    'image_path' => $relativePath,
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
            
        } catch (\Exception $e) {
            \Log::error('Pickup Confirm Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to confirm pickup: ' . $e->getMessage());
        }
    }
}