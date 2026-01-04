<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrate Users to Customer, Staff, Admin tables
        $this->migrateUsersToRoleTables();
        
        // Migrate Vehicles to Cars and Motorcycles
        $this->migrateVehiclesToCarsAndMotorcycles();
    }

    /**
     * Migrate users data to customer, staff, and admin tables based on role
     */
    private function migrateUsersToRoleTables(): void
    {
        // Get all users
        $users = DB::table('users')->get();
        
        foreach ($users as $user) {
            // Update username if null (use email or name)
            if (empty($user->username ?? null)) {
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['username' => $user->email ?? $user->name ?? 'user_' . $user->id]);
            }
            
            // Migrate to customer table
            if (($user->role ?? 'customer') === 'customer') {
                DB::table('customer')->updateOrInsert(
                    ['email' => $user->email],
                    [
                        'fullname' => $user->name ?? '',
                        'email' => $user->email,
                        'phone' => $user->phone ?? null,
                        'college' => $user->college ?? null,
                        'faculty' => $user->faculty ?? null,
                        'customer_type' => 'Student', // Default
                        'registration_date' => $user->created_at ? date('Y-m-d', strtotime($user->created_at)) : now(),
                        'created_at' => $user->created_at ?? now(),
                        'updated_at' => $user->updated_at ?? now(),
                    ]
                );
            }
            
            // Migrate to staff table
            if (($user->role ?? '') === 'staff') {
                DB::table('staff')->updateOrInsert(
                    ['user_id' => $user->id],
                    [
                        'user_id' => $user->id,
                        'fullname' => $user->name ?? '',
                        'email' => $user->email,
                        'phone' => $user->phone ?? null,
                        'is_active' => true,
                        'created_at' => $user->created_at ?? now(),
                        'updated_at' => $user->updated_at ?? now(),
                    ]
                );
            }
            
            // Migrate to admin table
            if (($user->role ?? '') === 'admin') {
                DB::table('admin')->updateOrInsert(
                    ['user_id' => $user->id],
                    [
                        'user_id' => $user->id,
                        'fullname' => $user->name ?? '',
                        'email' => $user->email,
                        'phone' => $user->phone ?? null,
                        'is_active' => true,
                        'created_at' => $user->created_at ?? now(),
                        'updated_at' => $user->updated_at ?? now(),
                    ]
                );
            }
        }
    }

    /**
     * Migrate vehicles data to cars and motorcycles based on vehicleType
     */
    private function migrateVehiclesToCarsAndMotorcycles(): void
    {
        // Check if vehicles table exists
        if (!Schema::hasTable('vehicles')) {
            return;
        }
        
        $vehicles = DB::table('vehicles')->get();
        
        foreach ($vehicles as $vehicle) {
            // Determine vehicle type
            $vehicleType = $vehicle->vehicleType ?? 
                          ($vehicle->vehicle_type ?? null) ??
                          'Car'; // Default to Car
            
            $vehicleType = strtolower($vehicleType);
            
            // Get plate number
            $plateNo = $vehicle->plate_no ?? 
                      ($vehicle->plate_number ?? null) ??
                      ($vehicle->registration_number ?? null);
            
            if (empty($plateNo)) {
                continue; // Skip if no plate number
            }
            
            // Get brand
            $brand = $vehicle->vehicle_brand ?? 
                    ($vehicle->brand ?? '') ?? '';
            
            // Get model
            $model = $vehicle->vehicle_model ?? 
                    ($vehicle->model ?? '') ?? '';
            
            // Get status
            $status = $vehicle->available_status ?? 
                    ($vehicle->availability_status ?? null) ??
                    ($vehicle->status ?? 'Available');
            
            // Get rental price
            $rentalPrice = $vehicle->rental_price ?? 
                          ($vehicle->daily_rate ?? null) ?? 0;
            
            // Get created date
            $createdDate = $vehicle->createdDate ?? 
                          ($vehicle->created_date ?? null) ??
                          ($vehicle->created_at ? date('Y-m-d', strtotime($vehicle->created_at)) : now());
            
            // Migrate to cars table
            if (in_array($vehicleType, ['car', 'cars', 'automobile', 'sedan', 'suv', 'hatchback']) && Schema::hasTable('cars')) {
                // Determine plate column name
                $plateColumn = Schema::hasColumn('cars', 'plate_no') ? 'plate_no' : 
                              (Schema::hasColumn('cars', 'plate_number') ? 'plate_number' : 'plate_no');
                
                $carData = [
                    $plateColumn => $plateNo,
                    'vehicle_brand' => $brand,
                    'vehicle_model' => $model,
                ];
                
                // Add status with correct column name
                if (Schema::hasColumn('cars', 'available_status')) {
                    $carData['available_status'] = $status;
                } else if (Schema::hasColumn('cars', 'availability_status')) {
                    $carData['availability_status'] = $status;
                }
                
                // Add date with correct column name
                if (Schema::hasColumn('cars', 'createdDate')) {
                    $carData['createdDate'] = $createdDate;
                } else if (Schema::hasColumn('cars', 'created_date')) {
                    $carData['created_date'] = $createdDate;
                }
                
                // Add rental price
                if (Schema::hasColumn('cars', 'rental_price')) {
                    $carData['rental_price'] = $rentalPrice;
                }
                
                // Add optional fields
                if (Schema::hasColumn('cars', 'manufacturing_year') && isset($vehicle->manufacturing_year)) {
                    $carData['manufacturing_year'] = $vehicle->manufacturing_year;
                }
                if (Schema::hasColumn('cars', 'color') && isset($vehicle->color)) {
                    $carData['color'] = $vehicle->color;
                }
                if (Schema::hasColumn('cars', 'engine_Capacity') && isset($vehicle->engine_Capacity)) {
                    $carData['engine_Capacity'] = $vehicle->engine_Capacity;
                }
                if (Schema::hasColumn('cars', 'vehicleType')) {
                    $carData['vehicleType'] = $vehicleType;
                } else if (Schema::hasColumn('cars', 'vehicle_type')) {
                    $carData['vehicle_type'] = $vehicleType;
                }
                if (Schema::hasColumn('cars', 'isActive')) {
                    $carData['isActive'] = $vehicle->isActive ?? true;
                }
                
                // Car-specific fields
                if (Schema::hasColumn('cars', 'seat_capacity')) {
                    $carData['seat_capacity'] = $vehicle->seat_capacity ?? 
                                               ($vehicle->seating_capacity ?? null) ?? null;
                } else if (Schema::hasColumn('cars', 'seating_capacity')) {
                    $carData['seating_capacity'] = $vehicle->seat_capacity ?? 
                                                  ($vehicle->seating_capacity ?? null) ?? null;
                }
                if (Schema::hasColumn('cars', 'transmission') && isset($vehicle->transmission)) {
                    $carData['transmission'] = $vehicle->transmission;
                }
                if (Schema::hasColumn('cars', 'model')) {
                    $carData['model'] = $model;
                }
                if (Schema::hasColumn('cars', 'car_type')) {
                    $carData['car_type'] = $vehicle->car_type ?? 
                                         ($vehicle->vehicle_type ?? null) ?? null;
                } else if (Schema::hasColumn('cars', 'vehicle_type')) {
                    $carData['vehicle_type'] = $vehicle->car_type ?? 
                                             ($vehicle->vehicle_type ?? null) ?? null;
                }
                if (Schema::hasColumn('cars', 'created_at')) {
                    $carData['created_at'] = $vehicle->created_at ?? now();
                    $carData['updated_at'] = $vehicle->updated_at ?? now();
                }
                
                DB::table('cars')->updateOrInsert(
                    [$plateColumn => $plateNo],
                    $carData
                );
            }
            
            // Migrate to motorcycles table
            if (in_array($vehicleType, ['motorcycle', 'motorcycles', 'bike', 'scooter', 'motorbike']) && Schema::hasTable('motorcycles')) {
                // Determine plate column name
                $plateColumn = Schema::hasColumn('motorcycles', 'plate_no') ? 'plate_no' : 
                              (Schema::hasColumn('motorcycles', 'plate_number') ? 'plate_number' : 'plate_no');
                
                $motorcycleData = [
                    $plateColumn => $plateNo,
                    'vehicle_brand' => $brand,
                    'vehicle_model' => $model,
                ];
                
                // Add status with correct column name
                if (Schema::hasColumn('motorcycles', 'available_status')) {
                    $motorcycleData['available_status'] = $status;
                } else if (Schema::hasColumn('motorcycles', 'availability_status')) {
                    $motorcycleData['availability_status'] = $status;
                }
                
                // Add date with correct column name
                if (Schema::hasColumn('motorcycles', 'createdDate')) {
                    $motorcycleData['createdDate'] = $createdDate;
                } else if (Schema::hasColumn('motorcycles', 'created_date')) {
                    $motorcycleData['created_date'] = $createdDate;
                }
                
                // Add rental price
                if (Schema::hasColumn('motorcycles', 'rental_price')) {
                    $motorcycleData['rental_price'] = $rentalPrice;
                }
                
                // Add optional fields
                if (Schema::hasColumn('motorcycles', 'manufacturing_year') && isset($vehicle->manufacturing_year)) {
                    $motorcycleData['manufacturing_year'] = $vehicle->manufacturing_year;
                }
                if (Schema::hasColumn('motorcycles', 'color') && isset($vehicle->color)) {
                    $motorcycleData['color'] = $vehicle->color;
                }
                if (Schema::hasColumn('motorcycles', 'engine_Capacity') && isset($vehicle->engine_Capacity)) {
                    $motorcycleData['engine_Capacity'] = $vehicle->engine_Capacity;
                }
                if (Schema::hasColumn('motorcycles', 'vehicleType')) {
                    $motorcycleData['vehicleType'] = $vehicleType;
                } else if (Schema::hasColumn('motorcycles', 'vehicle_type')) {
                    $motorcycleData['vehicle_type'] = $vehicleType;
                }
                if (Schema::hasColumn('motorcycles', 'isActive')) {
                    $motorcycleData['isActive'] = $vehicle->isActive ?? true;
                }
                
                // Motorcycle-specific fields
                if (Schema::hasColumn('motorcycles', 'motor_type')) {
                    $motorcycleData['motor_type'] = $vehicle->motor_type ?? 
                                                   ($vehicle->vehicle_type ?? null) ?? null;
                } else if (Schema::hasColumn('motorcycles', 'vehicle_type')) {
                    $motorcycleData['vehicle_type'] = $vehicle->motor_type ?? 
                                                     ($vehicle->vehicle_type ?? null) ?? null;
                }
                if (Schema::hasColumn('motorcycles', 'created_at')) {
                    $motorcycleData['created_at'] = $vehicle->created_at ?? now();
                    $motorcycleData['updated_at'] = $vehicle->updated_at ?? now();
                }
                
                DB::table('motorcycles')->updateOrInsert(
                    [$plateColumn => $plateNo],
                    $motorcycleData
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally clear migrated data
        // DB::table('customer')->truncate();
        // DB::table('staff')->truncate();
        // DB::table('admin')->truncate();
    }
};







