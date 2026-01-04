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
        // Check if motorcycles table exists
        if (!Schema::hasTable('motorcycles')) {
            Schema::create('motorcycles', function (Blueprint $table) {
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
                // Motorcycle-specific fields
                $table->string('motor_type')->nullable();
                $table->timestamps();
            });
        } else {
            // Update existing motorcycles table
            Schema::table('motorcycles', function (Blueprint $table) {
                // Add missing columns from vehicles structure
                if (!Schema::hasColumn('motorcycles', 'plate_no') && Schema::hasColumn('motorcycles', 'plate_number')) {
                    $table->renameColumn('plate_number', 'plate_no');
                }
                if (!Schema::hasColumn('motorcycles', 'available_status') && Schema::hasColumn('motorcycles', 'availability_status')) {
                    $table->renameColumn('availability_status', 'available_status');
                }
                if (!Schema::hasColumn('motorcycles', 'createdDate') && Schema::hasColumn('motorcycles', 'created_date')) {
                    $table->renameColumn('created_date', 'createdDate');
                }
                if (!Schema::hasColumn('motorcycles', 'rental_price')) {
                    $table->decimal('rental_price', 10, 2)->after('available_status');
                }
                if (!Schema::hasColumn('motorcycles', 'manufacturing_year')) {
                    $table->year('manufacturing_year')->nullable()->after('vehicle_model');
                }
                if (!Schema::hasColumn('motorcycles', 'color')) {
                    $table->string('color')->nullable()->after('manufacturing_year');
                }
                if (!Schema::hasColumn('motorcycles', 'engine_Capacity')) {
                    $table->string('engine_Capacity')->nullable()->after('color');
                }
                if (!Schema::hasColumn('motorcycles', 'vehicleType')) {
                    $table->string('vehicleType')->nullable()->after('engine_Capacity');
                }
                if (!Schema::hasColumn('motorcycles', 'isActive')) {
                    $table->boolean('isActive')->default(true)->after('rental_price');
                }
                
                // Motorcycle-specific fields
                if (!Schema::hasColumn('motorcycles', 'motor_type') && Schema::hasColumn('motorcycles', 'vehicle_type')) {
                    $table->renameColumn('vehicle_type', 'motor_type');
                } else if (!Schema::hasColumn('motorcycles', 'motor_type')) {
                    $table->string('motor_type')->nullable()->after('isActive');
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







