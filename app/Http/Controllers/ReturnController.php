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

        // Check if booking is ONGOING or can be returned
        // Allow ONGOING and Confirmed (in case pickup wasn't done through system)
        $allowedStatuses = ['Ongoing', 'Confirmed'];
        if (!in_array($booking->booking_status, $allowedStatuses)) {
            return redirect()->route('bookings.index')
                ->with('error', 'This booking cannot be returned. Current status: ' . $booking->booking_status);
        }

        // Check if already completed
        if ($booking->booking_status === 'Completed') {
            return redirect()->route('bookings.index')
                ->with('error', 'This booking has already been completed.');
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

        // E. Save Images to myportfolio public folder
        // Uploads are stored in: C:\xampp\htdocs\myportfolio\public\uploads\vehicle_conditions
        $imageFields = ['front_image', 'back_image', 'left_image', 'right_image', 'fuel_image'];

        foreach ($imageFields as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $fileName = time() . '_return_' . $field . '_' . $file->getClientOriginalName();
                
                // Upload to myportfolio public folder
                $path = $file->storeAs('uploads/vehicle_conditions', $fileName, 'wbl_public'); 

                VehicleConditionImage::create([
                    'image_path' => $path, 
                    'image_taken_time' => now(),
                    'formID' => $form->formID,
                ]);
            }
        }

        // Handle additional images
        if ($request->hasFile('additional_images')) {
            foreach ($request->file('additional_images') as $index => $file) {
                $fileName = time() . '_return_additional_' . $index . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('uploads/vehicle_conditions', $fileName, 'wbl_public');
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

        // G. ADD STAMP TO LOYALTY CARD
        $loyaltyCard = $booking->customer->loyaltyCard;
        if ($loyaltyCard) {
            $loyaltyCard->total_stamps += 1;
            $loyaltyCard->loyalty_last_updated = now();
            $loyaltyCard->save();
        }

        // H. Calculate Deposit Amount
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
/**
     * Handle deposit decision (add to wallet or request refund)
     */
    public function handleDeposit(Request $request, Booking $booking)
    {
        // 1. Verify ownership
        if ($booking->customer->userID !== auth()->id()) {
            abort(403, 'Unauthorized access');
        }

        // 2. SECURITY: Prevent Double Refund
        // If status is not 'pending' (meaning it's 'refunded' or something else), stop.
        // Note: Check your specific ENUM values. Usually distinct from booking_status.
        if ($booking->deposit_refund_status === 'refunded' || $booking->deposit_refund_status === 'pending') {
             return redirect()->route('bookings.index')
                 ->with('error', 'Action denied. This deposit has already been processed.');
        }

        $request->validate([
            'deposit_action' => 'required|in:wallet,refund',
        ]);

        // 3. Get Real Amount (Fixing the hardcoded 50)
        $depositAmount = $booking->deposit_amount ?? 0;

        if ($request->deposit_action === 'wallet') {
            // === OPTION A: HOLD IN WALLET (For next Auto-Booking) ===
            $wallet = $booking->customer->walletAccount;
            
            if ($wallet) {
                // Add to Wallet Balance (which acts as Holding Balance)
                $wallet->wallet_balance += $depositAmount;
                $wallet->wallet_lastUpdate_Date_Time = now();
                $wallet->save();

                // Update Booking: Mark as Refunded so they can't claim again
                $booking->deposit_refund_status = 'refunded'; 
                $booking->deposit_customer_choice = 'wallet'; 
                $booking->deposit_refund_amount = $depositAmount;
                $booking->save();

                return redirect()->route('bookings.index')
                    ->with('success', "RM " . number_format($depositAmount, 2) . " is now HELD in your wallet. It will auto-pay the deposit for your next booking!");
            } else {
                return redirect()->route('bookings.index')
                    ->with('error', 'Wallet account not found. Please contact support.');
            }
        } else {
            // === OPTION B: REQUEST CASH REFUND ===
            // Mark as 'pending' so Admin sees it in the Refund List
            $booking->deposit_refund_status = 'pending';
            $booking->deposit_customer_choice = 'bank_transfer';
            $booking->deposit_refund_amount = $depositAmount;
            $booking->save();

            return redirect()->route('bookings.index')
                ->with('info', 'Refund request received. Our team will process your bank transfer within 3-5 business days.');
        }
    }
}