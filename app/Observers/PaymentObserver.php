<?php

namespace App\Observers;

use App\Models\Payment;
use App\Models\AdminNotification;
use Illuminate\Support\Facades\Log;

class PaymentObserver
{
    /**
     * Handle the Payment "created" event.
     */
    public function created(Payment $payment): void
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('notification')) {
                return;
            }
            
            $booking = $payment->booking;
            if (!$booking) {
                return;
            }
            
            $customer = $booking->customer;
            $vehicle = $booking->vehicle;
            $vehicleInfo = $vehicle ? ($vehicle->vehicle_brand . ' ' . $vehicle->vehicle_model . ' (' . ($vehicle->plate_number ?? 'N/A') . ')') : 'N/A';
            $customerName = $customer && $customer->user ? $customer->user->name : 'Customer';
            $amount = $payment->total_amount ?? 0;
            $paymentMethod = $payment->payment_method ?? 'N/A';
            
            AdminNotification::create([
                'type' => 'new_payment',
                'notifiable_type' => 'admin',
                'notifiable_id' => null,
                'user_id' => $customer->userID ?? null,
                'booking_id' => $booking->bookingID,
                'payment_id' => $payment->paymentID,
                'message' => "New Payment RM " . number_format($amount, 2) . " for Booking #{$booking->bookingID} - {$vehicleInfo} by {$customerName}",
                'data' => [
                    'payment_id' => $payment->paymentID,
                    'booking_id' => $booking->bookingID,
                    'amount' => $amount,
                    'payment_method' => $paymentMethod,
                    'vehicle_info' => $vehicleInfo,
                    'customer_name' => $customerName,
                ],
                'is_read' => false,
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to create new payment notification: ' . $e->getMessage());
        }
    }
}

