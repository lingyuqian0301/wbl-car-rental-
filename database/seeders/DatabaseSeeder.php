<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * 
     * Run with: php artisan db:seed
     * Or specific seeder: php artisan db:seed --class=UserSeeder
     */
    public function run(): void
    {
        $this->call([
            // 1. Base data (no dependencies)
            PersonDetailsSeeder::class,
            
            // 2. Users (depends on PersonDetails for staff/admin)
            UserSeeder::class,
            
            // 3. Role assignments (depends on Users)
            AdminSeeder::class,
            StaffSeeder::class,
            
            // 4. Vehicle owners (no user dependency)
            OwnerCarSeeder::class,
            
            // 5. Vehicles (depends on OwnerCar)
            VehicleSeeder::class,
            
            // 6. Customers with wallets and loyalty (depends on Users)
            CustomerSeeder::class,
            
            // 7. Bookings (depends on Customers and Vehicles)
            BookingSeeder::class,
            
            // 8. Payments (depends on Bookings)
            PaymentSeeder::class,
        ]);
    }
}
