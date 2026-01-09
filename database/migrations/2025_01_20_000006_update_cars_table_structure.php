<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if cars table exists
        if (!Schema::hasTable('cars')) {
            Schema::create('cars', function (Blueprint $table) {
                $table->id('vehicleID');
                $table->string('plate_no')->unique();
                $table->enum('available_status', ['Available', 'Rented', 'Maintenance'])->default('Available');
                $table->date('createdDate')->nullable();
                $table->string('vehicle_brand');
                $table->string('vehicle_model');
                $table->year('manufacturing_year')->nullable();
                $table->string('color')->nullable();
                $table->string('engine_Capacity')->nullable();
                $table->string('vehicleType')->nullable();
                $table->decimal('rental_price', 10, 2);
                $table->boolean('isActive')->default(true);
                // Car-specific fields
                $table->integer('seat_capacity')->nullable();
                $table->string('transmission')->nullable();
                $table->string('model')->nullable();
                $table->string('car_type')->nullable();
                $table->timestamps();
            });
        } else {
            // Update existing cars table
            Schema::table('cars', function (Blueprint $table) {
                // Add missing columns from vehicles structure
                if (!Schema::hasColumn('cars', 'plate_no') && Schema::hasColumn('cars', 'plate_number')) {
                    $table->renameColumn('plate_number', 'plate_no');
                }
                if (!Schema::hasColumn('cars', 'available_status') && Schema::hasColumn('cars', 'availability_status')) {
                    $table->renameColumn('availability_status', 'available_status');
                }
                if (!Schema::hasColumn('cars', 'createdDate') && Schema::hasColumn('cars', 'created_date')) {
                    $table->renameColumn('created_date', 'createdDate');
                }
                if (!Schema::hasColumn('cars', 'rental_price') && Schema::hasColumn('cars', 'rental_price')) {
                    // Already exists
                } else if (!Schema::hasColumn('cars', 'rental_price')) {
                    $table->decimal('rental_price', 10, 2)->after('available_status');
                }
                if (!Schema::hasColumn('cars', 'manufacturing_year')) {
                    $table->year('manufacturing_year')->nullable()->after('vehicle_model');
                }
                if (!Schema::hasColumn('cars', 'engine_Capacity')) {
                    $table->string('engine_Capacity')->nullable()->after('color');
                }
                if (!Schema::hasColumn('cars', 'vehicleType')) {
                    $table->string('vehicleType')->nullable()->after('engine_Capacity');
                }
                if (!Schema::hasColumn('cars', 'isActive') && Schema::hasColumn('cars', 'isActive')) {
                    // Already exists
                } else if (!Schema::hasColumn('cars', 'isActive')) {
                    $table->boolean('isActive')->default(true)->after('rental_price');
                }
                
                // Car-specific fields
                if (!Schema::hasColumn('cars', 'seat_capacity') && Schema::hasColumn('cars', 'seating_capacity')) {
                    $table->renameColumn('seating_capacity', 'seat_capacity');
                } else if (!Schema::hasColumn('cars', 'seat_capacity')) {
                    $table->integer('seat_capacity')->nullable()->after('isActive');
                }
                if (!Schema::hasColumn('cars', 'transmission')) {
                    $table->string('transmission')->nullable()->after('seat_capacity');
                }
                if (!Schema::hasColumn('cars', 'model')) {
                    $table->string('model')->nullable()->after('transmission');
                }
                if (!Schema::hasColumn('cars', 'car_type') && Schema::hasColumn('cars', 'vehicle_type')) {
                    $table->renameColumn('vehicle_type', 'car_type');
                } else if (!Schema::hasColumn('cars', 'car_type')) {
                    $table->string('car_type')->nullable()->after('model');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert if needed
    }
};













