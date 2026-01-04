<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use App\Models\Car;
use App\Models\OwnerCar;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        $owner1 = OwnerCar::first();
        $owner2 = OwnerCar::skip(1)->first() ?? $owner1;

        $vehicles = [
            // Perodua
            [
                'plate_number' => 'JQA 1234',
                'availability_status' => 'Available',
                'vehicle_brand' => 'Perodua',
                'vehicle_model' => 'Myvi',
                'manufacturing_year' => 2022,
                'color' => 'White',
                'engineCapacity' => 1.3,
                'vehicleType' => 'Hatchback',
                'rental_price' => 80.00,
                'isActive' => true,
                'ownerID' => $owner1?->ownerID,
                'car' => ['seating_capacity' => 5, 'transmission' => 'Automatic', 'model' => 'G', 'car_type' => 'Hatchback'],
            ],
            [
                'plate_number' => 'JQB 5678',
                'availability_status' => 'Available',
                'vehicle_brand' => 'Perodua',
                'vehicle_model' => 'Axia',
                'manufacturing_year' => 2023,
                'color' => 'Red',
                'engineCapacity' => 1.0,
                'vehicleType' => 'Hatchback',
                'rental_price' => 60.00,
                'isActive' => true,
                'ownerID' => $owner1?->ownerID,
                'car' => ['seating_capacity' => 5, 'transmission' => 'Automatic', 'model' => 'E', 'car_type' => 'Hatchback'],
            ],
            [
                'plate_number' => 'JQC 9012',
                'availability_status' => 'Available',
                'vehicle_brand' => 'Perodua',
                'vehicle_model' => 'Bezza',
                'manufacturing_year' => 2021,
                'color' => 'Silver',
                'engineCapacity' => 1.3,
                'vehicleType' => 'Sedan',
                'rental_price' => 90.00,
                'isActive' => true,
                'ownerID' => $owner1?->ownerID,
                'car' => ['seating_capacity' => 5, 'transmission' => 'Automatic', 'model' => 'X', 'car_type' => 'Sedan'],
            ],
            // Proton
            [
                'plate_number' => 'JQD 3456',
                'availability_status' => 'Available',
                'vehicle_brand' => 'Proton',
                'vehicle_model' => 'Saga',
                'manufacturing_year' => 2022,
                'color' => 'Blue',
                'engineCapacity' => 1.3,
                'vehicleType' => 'Sedan',
                'rental_price' => 75.00,
                'isActive' => true,
                'ownerID' => $owner2?->ownerID,
                'car' => ['seating_capacity' => 5, 'transmission' => 'Automatic', 'model' => 'Premium', 'car_type' => 'Sedan'],
            ],
            [
                'plate_number' => 'JQE 7890',
                'availability_status' => 'Rented',
                'vehicle_brand' => 'Proton',
                'vehicle_model' => 'X50',
                'manufacturing_year' => 2023,
                'color' => 'Black',
                'engineCapacity' => 1.5,
                'vehicleType' => 'SUV',
                'rental_price' => 150.00,
                'isActive' => true,
                'ownerID' => $owner2?->ownerID,
                'car' => ['seating_capacity' => 5, 'transmission' => 'Automatic', 'model' => 'Flagship', 'car_type' => 'SUV'],
            ],
            [
                'plate_number' => 'JQF 1357',
                'availability_status' => 'Available',
                'vehicle_brand' => 'Proton',
                'vehicle_model' => 'X70',
                'manufacturing_year' => 2022,
                'color' => 'Grey',
                'engineCapacity' => 1.8,
                'vehicleType' => 'SUV',
                'rental_price' => 180.00,
                'isActive' => true,
                'ownerID' => $owner2?->ownerID,
                'car' => ['seating_capacity' => 5, 'transmission' => 'Automatic', 'model' => 'Premium X', 'car_type' => 'SUV'],
            ],
            // Toyota
            [
                'plate_number' => 'JQG 2468',
                'availability_status' => 'Available',
                'vehicle_brand' => 'Toyota',
                'vehicle_model' => 'Vios',
                'manufacturing_year' => 2023,
                'color' => 'White',
                'engineCapacity' => 1.5,
                'vehicleType' => 'Sedan',
                'rental_price' => 120.00,
                'isActive' => true,
                'ownerID' => $owner1?->ownerID,
                'car' => ['seating_capacity' => 5, 'transmission' => 'Automatic', 'model' => 'G', 'car_type' => 'Sedan'],
            ],
            [
                'plate_number' => 'JQH 3579',
                'availability_status' => 'Maintenance',
                'vehicle_brand' => 'Toyota',
                'vehicle_model' => 'Yaris',
                'manufacturing_year' => 2021,
                'color' => 'Red',
                'engineCapacity' => 1.5,
                'vehicleType' => 'Hatchback',
                'rental_price' => 100.00,
                'isActive' => true,
                'ownerID' => $owner1?->ownerID,
                'car' => ['seating_capacity' => 5, 'transmission' => 'Automatic', 'model' => 'E', 'car_type' => 'Hatchback'],
            ],
            // Honda
            [
                'plate_number' => 'JQI 4680',
                'availability_status' => 'Available',
                'vehicle_brand' => 'Honda',
                'vehicle_model' => 'City',
                'manufacturing_year' => 2023,
                'color' => 'Silver',
                'engineCapacity' => 1.5,
                'vehicleType' => 'Sedan',
                'rental_price' => 130.00,
                'isActive' => true,
                'ownerID' => $owner2?->ownerID,
                'car' => ['seating_capacity' => 5, 'transmission' => 'Automatic', 'model' => 'V', 'car_type' => 'Sedan'],
            ],
            [
                'plate_number' => 'JQJ 5791',
                'availability_status' => 'Available',
                'vehicle_brand' => 'Honda',
                'vehicle_model' => 'HR-V',
                'manufacturing_year' => 2022,
                'color' => 'Blue',
                'engineCapacity' => 1.8,
                'vehicleType' => 'SUV',
                'rental_price' => 170.00,
                'isActive' => true,
                'ownerID' => $owner2?->ownerID,
                'car' => ['seating_capacity' => 5, 'transmission' => 'Automatic', 'model' => 'RS', 'car_type' => 'SUV'],
            ],
        ];

        foreach ($vehicles as $vehicleData) {
            $carData = $vehicleData['car'];
            unset($vehicleData['car']);

            $vehicleData['created_date'] = now()->subMonths(rand(1, 24));

            $vehicle = Vehicle::firstOrCreate(
                ['plate_number' => $vehicleData['plate_number']],
                $vehicleData
            );

            // Create Car record
            Car::firstOrCreate(
                ['vehicleID' => $vehicle->vehicleID],
                array_merge(['vehicleID' => $vehicle->vehicleID], $carData)
            );
        }
    }
}

