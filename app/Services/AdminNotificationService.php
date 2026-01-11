<?php

namespace App\Services;

use App\Models\AdminNotification;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AdminNotificationService
{
    /**
     * Create notification for new booking
     */
    public static function notifyNewBooking(Booking $booking): ?AdminNotification
    {
        try {
            if (!Schema::hasTable('notification')) {
                return null;
            }
            
            $vehicle = $booking->vehicle;
            $vehicleInfo = $vehicle ? ($vehicle->vehicle_brand . ' ' . $vehicle->vehicle_model . ' (' . ($vehicle->plate_number ?? 'N/A') . ')') : 'N/A';
            $customer = $booking->customer;
            $customerName = $customer && $customer->user ? $customer->user->name : 'Customer';
            
            return AdminNotification::create([
                'type' => 'new_booking',
                'notifiable_type' => 'admin',
                'notifiable_id' => null,
                'user_id' => $customer->userID ?? null,
                'booking_id' => $booking->bookingID,
                'payment_id' => null,
                'message' => "New Booking #{$booking->bookingID} - {$customerName} booked {$vehicleInfo}",
                'data' => [
                    'booking_id' => $booking->bookingID,
                    'vehicle_info' => $vehicleInfo,
                    'customer_name' => $customerName,
                    'pickup_date' => $booking->rental_start_date,
                    'return_date' => $booking->rental_end_date,
                    'amount' => $booking->rental_amount,
                ],
                'is_read' => false,
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to create new booking notification: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create notification for cancellation request
     */
    public static function notifyNewCancellation(Booking $booking): ?AdminNotification
    {
        try {
            if (!Schema::hasTable('notification')) {
                return null;
            }
            
            $vehicle = $booking->vehicle;
            $vehicleInfo = $vehicle ? ($vehicle->vehicle_brand . ' ' . $vehicle->vehicle_model . ' (' . ($vehicle->plate_number ?? 'N/A') . ')') : 'N/A';
            $customer = $booking->customer;
            $customerName = $customer && $customer->user ? $customer->user->name : 'Customer';
            
            return AdminNotification::create([
                'type' => 'new_cancellation',
                'notifiable_type' => 'admin',
                'notifiable_id' => null,
                'user_id' => $customer->userID ?? null,
                'booking_id' => $booking->bookingID,
                'payment_id' => null,
                'message' => "Cancellation Request - Booking #{$booking->bookingID} by {$customerName} for {$vehicleInfo}",
                'data' => [
                    'booking_id' => $booking->bookingID,
                    'vehicle_info' => $vehicleInfo,
                    'customer_name' => $customerName,
                    'cancellation_reason' => $booking->cancellation_reason ?? null,
                ],
                'is_read' => false,
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to create cancellation notification: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create notification for new payment
     */
    public static function notifyNewPayment(Payment $payment): ?AdminNotification
    {
        try {
            if (!Schema::hasTable('notification')) {
                return null;
            }
            
            $booking = $payment->booking;
            $vehicle = $booking ? $booking->vehicle : null;
            $vehicleInfo = $vehicle ? ($vehicle->vehicle_brand . ' ' . $vehicle->vehicle_model . ' (' . ($vehicle->plate_number ?? 'N/A') . ')') : 'N/A';
            $customer = $booking ? $booking->customer : null;
            $customerName = $customer && $customer->user ? $customer->user->name : 'Customer';
            $amount = $payment->total_amount ?? 0;
            
            return AdminNotification::create([
                'type' => 'new_payment',
                'notifiable_type' => 'admin',
                'notifiable_id' => null,
                'user_id' => $customer->userID ?? null,
                'booking_id' => $booking->bookingID ?? null,
                'payment_id' => $payment->paymentID ?? $payment->id ?? null,
                'message' => "New Payment - RM " . number_format($amount, 2) . " received for Booking #{$booking->bookingID} from {$customerName}",
                'data' => [
                    'payment_id' => $payment->paymentID ?? $payment->id,
                    'booking_id' => $booking->bookingID ?? null,
                    'amount' => $amount,
                    'payment_method' => $payment->payment_method ?? null,
                    'customer_name' => $customerName,
                    'vehicle_info' => $vehicleInfo,
                ],
                'is_read' => false,
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to create new payment notification: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create notification for booking date change
     */
    public static function notifyDateChange(Booking $booking, $oldStartDate, $oldEndDate): ?AdminNotification
    {
        try {
            if (!Schema::hasTable('notification')) {
                return null;
            }
            
            $vehicle = $booking->vehicle;
            $vehicleInfo = $vehicle ? ($vehicle->vehicle_brand . ' ' . $vehicle->vehicle_model . ' (' . ($vehicle->plate_number ?? 'N/A') . ')') : 'N/A';
            $customer = $booking->customer;
            $customerName = $customer && $customer->user ? $customer->user->name : 'Customer';
            
            $oldStart = \Carbon\Carbon::parse($oldStartDate)->format('d M Y');
            $oldEnd = \Carbon\Carbon::parse($oldEndDate)->format('d M Y');
            $newStart = \Carbon\Carbon::parse($booking->rental_start_date)->format('d M Y');
            $newEnd = \Carbon\Carbon::parse($booking->rental_end_date)->format('d M Y');
            
            return AdminNotification::create([
                'type' => 'booking_date_change',
                'notifiable_type' => 'admin',
                'notifiable_id' => null,
                'user_id' => $customer->userID ?? null,
                'booking_id' => $booking->bookingID,
                'payment_id' => null,
                'message' => "Date Changed - Booking #{$booking->bookingID} dates updated from {$oldStart}-{$oldEnd} to {$newStart}-{$newEnd}",
                'data' => [
                    'booking_id' => $booking->bookingID,
                    'vehicle_info' => $vehicleInfo,
                    'customer_name' => $customerName,
                    'old_start_date' => $oldStartDate,
                    'old_end_date' => $oldEndDate,
                    'new_start_date' => $booking->rental_start_date,
                    'new_end_date' => $booking->rental_end_date,
                ],
                'is_read' => false,
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to create date change notification: ' . $e->getMessage());
            return null;
        }
    }
}

