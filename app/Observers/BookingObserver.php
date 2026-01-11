<?php

namespace App\Observers;

use App\Models\Booking;
use App\Models\AdminNotification;
use Illuminate\Support\Facades\Log;

class BookingObserver
{
    /**
     * Handle the Booking "created" event.
     */
    public function created(Booking $booking): void
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('notification')) {
                return;
            }
            
            $customer = $booking->customer;
            $vehicle = $booking->vehicle;
            $vehicleInfo = $vehicle ? ($vehicle->vehicle_brand . ' ' . $vehicle->vehicle_model . ' (' . ($vehicle->plate_number ?? 'N/A') . ')') : 'N/A';
            $customerName = $customer && $customer->user ? $customer->user->name : 'Customer';
            
            AdminNotification::create([
                'type' => 'new_booking',
                'notifiable_type' => 'admin',
                'notifiable_id' => null,
                'user_id' => $customer->userID ?? null,
                'booking_id' => $booking->bookingID,
                'payment_id' => null,
                'message' => "New Booking #{$booking->bookingID} - {$vehicleInfo} by {$customerName}",
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
        }
    }

    /**
     * Handle the Booking "updated" event.
     */
    public function updated(Booking $booking): void
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('notification')) {
                return;
            }
            
            $customer = $booking->customer;
            $vehicle = $booking->vehicle;
            $vehicleInfo = $vehicle ? ($vehicle->vehicle_brand . ' ' . $vehicle->vehicle_model . ' (' . ($vehicle->plate_number ?? 'N/A') . ')') : 'N/A';
            $customerName = $customer && $customer->user ? $customer->user->name : 'Customer';
            
            // Check if dates were changed
            $originalPickupDate = $booking->getOriginal('rental_start_date');
            $originalReturnDate = $booking->getOriginal('rental_end_date');
            $newPickupDate = $booking->rental_start_date;
            $newReturnDate = $booking->rental_end_date;
            
            $pickupChanged = $originalPickupDate != $newPickupDate;
            $returnChanged = $originalReturnDate != $newReturnDate;
            
            if ($pickupChanged || $returnChanged) {
                $changes = [];
                if ($pickupChanged) {
                    $changes[] = "Pickup: " . ($originalPickupDate ? \Carbon\Carbon::parse($originalPickupDate)->format('d M Y') : 'N/A') . 
                                 " → " . ($newPickupDate ? \Carbon\Carbon::parse($newPickupDate)->format('d M Y') : 'N/A');
                }
                if ($returnChanged) {
                    $changes[] = "Return: " . ($originalReturnDate ? \Carbon\Carbon::parse($originalReturnDate)->format('d M Y') : 'N/A') . 
                                 " → " . ($newReturnDate ? \Carbon\Carbon::parse($newReturnDate)->format('d M Y') : 'N/A');
                }
                
                AdminNotification::create([
                    'type' => 'booking_date_changed',
                    'notifiable_type' => 'admin',
                    'notifiable_id' => null,
                    'user_id' => $customer->userID ?? null,
                    'booking_id' => $booking->bookingID,
                    'payment_id' => null,
                    'message' => "Booking #{$booking->bookingID} dates changed - " . implode(', ', $changes),
                    'data' => [
                        'booking_id' => $booking->bookingID,
                        'vehicle_info' => $vehicleInfo,
                        'customer_name' => $customerName,
                        'original_pickup_date' => $originalPickupDate,
                        'original_return_date' => $originalReturnDate,
                        'new_pickup_date' => $newPickupDate,
                        'new_return_date' => $newReturnDate,
                    ],
                    'is_read' => false,
                ]);
            }
            
            // Check if booking was cancelled
            $originalStatus = $booking->getOriginal('booking_status');
            $newStatus = $booking->booking_status;
            
            if ($originalStatus !== 'Cancelled' && $newStatus === 'Cancelled') {
                AdminNotification::create([
                    'type' => 'new_cancellation',
                    'notifiable_type' => 'admin',
                    'notifiable_id' => null,
                    'user_id' => $customer->userID ?? null,
                    'booking_id' => $booking->bookingID,
                    'payment_id' => null,
                    'message' => "Booking #{$booking->bookingID} - {$vehicleInfo} has been cancelled by {$customerName}",
                    'data' => [
                        'booking_id' => $booking->bookingID,
                        'vehicle_info' => $vehicleInfo,
                        'customer_name' => $customerName,
                        'pickup_date' => $booking->rental_start_date,
                        'return_date' => $booking->rental_end_date,
                    ],
                    'is_read' => false,
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to create booking update notification: ' . $e->getMessage());
        }
    }
}
