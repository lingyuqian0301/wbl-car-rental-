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
            'additional_images.*' => 'nullable|image|max:5120',
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

        // --- PRE-PROCESS FUEL IMAGE (Required by database) ---
        $fuelImgPath = '';
        $destinationPath = public_path('images/vehicle_conditions');

        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        if ($request->hasFile('fuel_image')) {
            $file = $request->file('fuel_image');
            $filename = uniqid() . '_' . time() . '_fuel_image_return.' . $file->getClientOriginalExtension();
            $file->move($destinationPath, $filename);
            $fuelImgPath = 'images/vehicle_conditions/' . $filename;
        }

        // D. Create Vehicle Condition Form (RETURN type)
        $form = VehicleConditionForm::create([
            'form_type' => 'RETURN',
            'odometer_reading' => $request->mileage,
            'fuel_level' => $fuelEnum,
            'scratches_notes' => $request->remarks,
            'reported_dated_time' => $request->date_check,
            'bookingID' => $booking->bookingID,
            'rental_agreement' => true,    // Added missing default value
            'fuel_img' => $fuelImgPath,    // Added missing fuel_img path
        ]);

        // Create the VehicleConditionImage entry for fuel explicitly
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
                $filename = uniqid() . '_' . time() . '_' . $field . '_return.' . $file->getClientOriginalExtension();
                
                // Move file to public/images/vehicle_conditions
                $file->move($destinationPath, $filename);
                
                // Store the relative URL path in database
                $relativePath = 'images/vehicle_conditions/' . $filename;

                VehicleConditionImage::create([
                    'image_path' => $relativePath, 
                    'image_taken_time' => now(),
                    'formID' => $form->formID,
                ]);
            }
        }

        // Handle additional images
        if ($request->hasFile('additional_images')) {
            foreach ($request->file('additional_images') as $index => $file) {
                $filename = uniqid() . '_' . time() . '_additional_return_' . $index . '.' . $file->getClientOriginalExtension();
                $file->move($destinationPath, $filename);
                $relativePath = 'images/vehicle_conditions/' . $filename;

                VehicleConditionImage::create([
                    'image_path' => $relativePath,
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

        // H. Check if wallet deposit was used for this booking
        $depositAmount = $booking->deposit_amount ?? 50;
        $totalCost = $booking->total_amount ?? $booking->rental_amount;
        
        // Sum verified payments
        $verifiedPaid = $booking->payments()
            ->where('payment_status', 'Verified')
            ->sum('total_amount');
        
        // Check if there's a payment record for the deposit amount
        $hasDepositPayment = $booking->payments()
            ->where('payment_status', 'Verified')
            ->get()
            ->filter(function($p) use ($depositAmount) {
                return abs($p->total_amount - $depositAmount) < 5;
            })->count() > 0;
        
        // Wallet deposit was used if no deposit payment exists
        // (Wallet deductions don't create payment records)
        $walletDepositUsed = !$hasDepositPayment && $verifiedPaid < $totalCost;

        // If wallet deposit was used, skip the deposit options page
        // because the deposit money was already from their wallet
        if ($walletDepositUsed) {
            // Mark deposit as handled (no refund needed - it was from wallet)
            $booking->deposit_refund_status = 'refunded';
            $booking->deposit_customer_choice = 'wallet_used';
            $booking->deposit_refund_amount = 0;
            $booking->save();

            return redirect()->route('bookings.index')
                ->with('success', 'Vehicle return confirmed successfully! Your booking is now completed.');
        }

        // If regular deposit was paid (via bank transfer), show deposit options
        return redirect()->route('return.deposit', $booking->bookingID)
            ->with('success', 'Vehicle return confirmed successfully!')
            ->with('deposit_amount', $depositAmount);
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
        // 1. Verify ownership
        if ($booking->customer->userID !== auth()->id()) {
            abort(403, 'Unauthorized access');
        }

        // 2. SECURITY: Prevent Double Refund
        if ($booking->deposit_refund_status === 'refunded' || $booking->deposit_refund_status === 'pending') {
             return redirect()->route('bookings.index')
                 ->with('error', 'Action denied. This deposit has already been processed.');
        }

        $request->validate([
            'deposit_action' => 'required|in:wallet,refund',
        ]);

        // 3. Get Real Amount
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