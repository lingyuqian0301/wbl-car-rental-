<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $customers = Customer::all();
        $vehicles = Vehicle::all();

        if ($customers->isEmpty() || $vehicles->isEmpty()) {
            return;
        }

        $bookings = [
            // Past completed bookings
            [
                'customerID' => $customers[0]->customerID ?? 1,
                'vehicleID' => $vehicles[0]->vehicleID ?? 1,
                'rental_start_date' => Carbon::now()->subDays(30),
                'rental_end_date' => Carbon::now()->subDays(27),
                'duration' => 3,
                'deposit_amount' => 100.00,
                'rental_amount' => 240.00,
                'pickup_point' => 'UTM Main Gate, Skudai',
                'return_point' => 'UTM Main Gate, Skudai',
                'booking_status' => 'Completed',
                'lastUpdateDate' => Carbon::now()->subDays(27),
            ],
            [
                'customerID' => $customers[1]->customerID ?? 1,
                'vehicleID' => $vehicles[1]->vehicleID ?? 1,
                'rental_start_date' => Carbon::now()->subDays(20),
                'rental_end_date' => Carbon::now()->subDays(18),
                'duration' => 2,
                'deposit_amount' => 80.00,
                'rental_amount' => 120.00,
                'pickup_point' => 'Johor Bahru Sentral',
                'return_point' => 'Johor Bahru Sentral',
                'booking_status' => 'Completed',
                'lastUpdateDate' => Carbon::now()->subDays(18),
            ],
            // Current/Active bookings
            [
                'customerID' => $customers[2]->customerID ?? 1,
                'vehicleID' => $vehicles[4]->vehicleID ?? 1, // The X50 that's "Rented"
                'rental_start_date' => Carbon::now()->subDays(2),
                'rental_end_date' => Carbon::now()->addDays(3),
                'duration' => 5,
                'deposit_amount' => 200.00,
                'rental_amount' => 750.00,
                'pickup_point' => 'KLIA2 Airport',
                'return_point' => 'KLIA2 Airport',
                'booking_status' => 'Confirmed',
                'lastUpdateDate' => Carbon::now()->subDays(2),
            ],
            // Pending bookings
            [
                'customerID' => $customers[0]->customerID ?? 1,
                'vehicleID' => $vehicles[2]->vehicleID ?? 1,
                'rental_start_date' => Carbon::now()->addDays(1),
                'rental_end_date' => Carbon::now()->addDays(4),
                'duration' => 3,
                'deposit_amount' => 120.00,
                'rental_amount' => 270.00,
                'pickup_point' => 'UTM KL Campus',
                'return_point' => 'UTM Main Gate, Skudai',
                'booking_status' => 'Pending',
                'lastUpdateDate' => Carbon::now(),
            ],
            [
                'customerID' => $customers[3]->customerID ?? 1,
                'vehicleID' => $vehicles[5]->vehicleID ?? 1,
                'rental_start_date' => Carbon::now()->addDays(2),
                'rental_end_date' => Carbon::now()->addDays(7),
                'duration' => 5,
                'deposit_amount' => 250.00,
                'rental_amount' => 900.00,
                'pickup_point' => 'Senai Airport',
                'return_point' => 'Senai Airport',
                'booking_status' => 'Pending',
                'lastUpdateDate' => Carbon::now(),
            ],
            // Upcoming confirmed bookings
            [
                'customerID' => $customers[1]->customerID ?? 1,
                'vehicleID' => $vehicles[6]->vehicleID ?? 1,
                'rental_start_date' => Carbon::now()->addDays(3),
                'rental_end_date' => Carbon::now()->addDays(5),
                'duration' => 2,
                'deposit_amount' => 150.00,
                'rental_amount' => 240.00,
                'pickup_point' => 'Johor Premium Outlets',
                'return_point' => 'Johor Premium Outlets',
                'booking_status' => 'Confirmed',
                'lastUpdateDate' => Carbon::now()->subDays(1),
            ],
        ];

        foreach ($bookings as $bookingData) {
            Booking::firstOrCreate(
                [
                    'customerID' => $bookingData['customerID'],
                    'vehicleID' => $bookingData['vehicleID'],
                    'rental_start_date' => $bookingData['rental_start_date'],
                ],
                $bookingData
            );
        }
    }
}

