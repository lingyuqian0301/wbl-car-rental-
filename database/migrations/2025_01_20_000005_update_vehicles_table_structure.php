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
        // Check if vehicles table exists, if not create it
        if (!Schema::hasTable('vehicles')) {
            Schema::create('vehicles', function (Blueprint $table) {
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
                $table->timestamps();
            });
        } else {
            // Update existing vehicles table
            Schema::table('vehicles', function (Blueprint $table) {
                // Rename columns if needed
                if (Schema::hasColumn('vehicles', 'registration_number') && !Schema::hasColumn('vehicles', 'plate_no')) {
                    $table->renameColumn('registration_number', 'plate_no');
                }
                if (Schema::hasColumn('vehicles', 'status') && !Schema::hasColumn('vehicles', 'available_status')) {
                    $table->renameColumn('status', 'available_status');
                }
                if (Schema::hasColumn('vehicles', 'brand') && !Schema::hasColumn('vehicles', 'vehicle_brand')) {
                    $table->renameColumn('brand', 'vehicle_brand');
                }
                if (Schema::hasColumn('vehicles', 'model') && !Schema::hasColumn('vehicles', 'vehicle_model')) {
                    $table->renameColumn('model', 'vehicle_model');
                }
                if (Schema::hasColumn('vehicles', 'daily_rate') && !Schema::hasColumn('vehicles', 'rental_price')) {
                    $table->renameColumn('daily_rate', 'rental_price');
                }
                
                // Add missing columns
                if (!Schema::hasColumn('vehicles', 'createdDate')) {
                    $table->date('createdDate')->nullable()->after('available_status');
                }
                if (!Schema::hasColumn('vehicles', 'manufacturing_year')) {
                    $table->year('manufacturing_year')->nullable()->after('vehicle_model');
                }
                if (!Schema::hasColumn('vehicles', 'color')) {
                    $table->string('color')->nullable()->after('manufacturing_year');
                }
                if (!Schema::hasColumn('vehicles', 'engine_Capacity')) {
                    $table->string('engine_Capacity')->nullable()->after('color');
                }
                if (!Schema::hasColumn('vehicles', 'vehicleType')) {
                    $table->string('vehicleType')->nullable()->after('engine_Capacity');
                }
                if (!Schema::hasColumn('vehicles', 'isActive')) {
                    $table->boolean('isActive')->default(true)->after('rental_price');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert changes if needed
        Schema::table('vehicles', function (Blueprint $table) {
            if (Schema::hasColumn('vehicles', 'plate_no') && !Schema::hasColumn('vehicles', 'registration_number')) {
                $table->renameColumn('plate_no', 'registration_number');
            }
            if (Schema::hasColumn('vehicles', 'available_status') && !Schema::hasColumn('vehicles', 'status')) {
                $table->renameColumn('available_status', 'status');
            }
            if (Schema::hasColumn('vehicles', 'vehicle_brand') && !Schema::hasColumn('vehicles', 'brand')) {
                $table->renameColumn('vehicle_brand', 'brand');
            }
            if (Schema::hasColumn('vehicles', 'vehicle_model') && !Schema::hasColumn('vehicles', 'model')) {
                $table->renameColumn('vehicle_model', 'model');
            }
            if (Schema::hasColumn('vehicles', 'rental_price') && !Schema::hasColumn('vehicles', 'daily_rate')) {
                $table->renameColumn('rental_price', 'daily_rate');
            }
        });
    }
};







