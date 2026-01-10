<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\VehicleConditionForm;
use App\Models\VehicleConditionImage;
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

        // Check if booking is ONGOING
        if ($booking->booking_status !== 'Ongoing') {
            return redirect()->route('bookings.index')
                ->with('error', 'This booking is not currently active.');
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
        // A. Security Check
        if ($booking->customer->userID !== auth()->id()) {
            abort(403, 'Unauthorized access');
        }

        // B. Validation
        $validated = $request->validate([
            'confirm_return' => 'required|accepted',
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
        ], [
            'confirm_return.required' => 'You must confirm the vehicle return',
            'confirm_return.accepted' => 'You must accept the confirmation',
        ]);

        // C. Fuel Mapping Logic
        $fuelVal = (int) $request->fuel_level;
        $fuelEnum = 'EMPTY';
        if ($fuelVal >= 88) $fuelEnum = 'FULL';
        elseif ($fuelVal >= 63) $fuelEnum = '3/4';
        elseif ($fuelVal >= 38) $fuelEnum = '1/2';
        elseif ($fuelVal >= 13) $fuelEnum = '1/4';

        // D. Create Vehicle Condition Form (RETURN type)
        $form = VehicleConditionForm::create([
            'form_type' => 'RETURN',
            'odometer_reading' => $request->mileage,
            'fuel_level' => $fuelEnum,
            'scratches_notes' => $request->remarks,
            'reported_dated_time' => $request->date_check,
            'bookingID' => $booking->bookingID,
        ]);

        // E. Save Images
        $imageFields = ['front_image', 'back_image', 'left_image', 'right_image', 'fuel_image'];

        foreach ($imageFields as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $path = $file->store('vehicle_conditions', 'public'); 

                VehicleConditionImage::create([
                    'image_path' => $path, 
                    'image_taken_time' => now(),
                    'formID' => $form->formID,
                ]);
            }
        }

        // Handle additional images
        if ($request->hasFile('additional_images')) {
            foreach ($request->file('additional_images') as $file) {
                $path = $file->store('vehicle_conditions', 'public');
                VehicleConditionImage::create([
                    'image_path' => $path,
                    'image_taken_time' => now(),
                    'formID' => $form->formID,
                ]);
            }
        }

        // F. UPDATE BOOKING STATUS TO COMPLETED
        $booking->booking_status = 'Completed';
        $booking->save();

        // G. Calculate Deposit Amount
        $depositAmount = $booking->deposit_amount ?? 0;

        // Redirect to deposit handling page with deposit info
        return redirect()->route('return.deposit', $booking->bookingID)
            ->with('success', 'Vehicle return confirmed successfully!')
            ->with('deposit_amount', 50);
    }

    /**
     * Show deposit handling options
     */
    public function showDepositOptions(Booking $booking)
    {
        // Verify ownership
        if ($booking->customer->userID !== auth()->id()) {
            abort(403, 'Unauthorized access');
        }

        // Check if booking is completed
        if ($booking->booking_status !== 'Completed') {
            return redirect()->route('bookings.index')
                ->with('error', 'This booking is not completed yet.');
        }

        $depositAmount = $booking->deposit_amount ?? 0;

        return view('bookings.deposit-options', compact('booking', 'depositAmount'));
    }

    /**
     * Handle deposit decision (add to wallet or request refund)
     */
    public function handleDeposit(Request $request, Booking $booking)
    {
        // Verify ownership
        if ($booking->customer->userID !== auth()->id()) {
            abort(403, 'Unauthorized access');
        }

        $request->validate([
            'deposit_action' => 'required|in:wallet,refund',
        ]);

        $depositAmount = $booking->deposit_amount ?? 0;

        if ($request->deposit_action === 'wallet') {
            // Add deposit to wallet balance
            $wallet = $booking->customer->walletAccount;
            
            if ($wallet) {
                $wallet->wallet_balance += 50;
                $wallet->wallet_lastUpdate_Date_Time = now();
                $wallet->save();

                return redirect()->route('bookings.index')
                    ->with('success', "RM 50 has been added to your wallet balance!");
            } else {
                return redirect()->route('bookings.index')
                    ->with('error', 'Wallet account not found. Please contact support.');
            }
        } else {
            // Redirect to refund page (when available)
            // For now, show a message
            return redirect()->route('bookings.index')
                ->with('info', 'Refund request received. Our team will process your refund within 3-5 business days.');
            
            // TODO: When refund functionality is ready, uncomment this:
            // return redirect()->route('refunds.create', ['booking' => $booking->bookingID]);
        }
    }
}