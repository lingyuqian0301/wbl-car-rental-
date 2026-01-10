<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Modify Car_Img table to allow documentID to store Google Drive URLs as strings
     */
    public function up(): void
    {
        // Check if Car_Img table exists
        if (Schema::hasTable('Car_Img')) {
            Schema::table('Car_Img', function (Blueprint $table) {
                // Drop foreign key constraint first (if exists)
                try {
                    $table->dropForeign(['documentID']);
                } catch (\Exception $e) {
                    // Foreign key might not exist, continue
                }
            });
            
            // Modify column to text to store URL
            DB::statement('ALTER TABLE `Car_Img` MODIFY COLUMN `documentID` TEXT NULL;');
            
            // Add vehicleID column if it doesn't exist (to link directly to vehicle)
            if (!Schema::hasColumn('Car_Img', 'vehicleID')) {
                Schema::table('Car_Img', function (Blueprint $table) {
                    $table->unsignedInteger('vehicleID')->nullable()->after('imgID');
                    $table->foreign('vehicleID')->references('vehicleID')->on('Vehicle')->onDelete('cascade');
                });
            }
        }
        
        // Also check car_img (lowercase) if it exists
        if (Schema::hasTable('car_img')) {
            Schema::table('car_img', function (Blueprint $table) {
                // Drop foreign key constraint if exists
                try {
                    $table->dropForeign(['documentID']);
                } catch (\Exception $e) {
                    // Foreign key might not exist, continue
                }
            });
            
            // Modify column to text to store URL
            DB::statement('ALTER TABLE `car_img` MODIFY COLUMN `documentID` TEXT NULL;');
            
            // Add vehicleID column if it doesn't exist
            if (!Schema::hasColumn('car_img', 'vehicleID')) {
                Schema::table('car_img', function (Blueprint $table) {
                    $table->unsignedInteger('vehicleID')->nullable()->after('imgID');
                    $table->foreign('vehicleID')->references('vehicleID')->on('Vehicle')->onDelete('cascade');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to integer foreign key (this might fail if data exists)
        if (Schema::hasTable('Car_Img')) {
            DB::statement('ALTER TABLE `Car_Img` MODIFY COLUMN `documentID` UNSIGNED INTEGER NULL;');
            
            Schema::table('Car_Img', function (Blueprint $table) {
                $table->foreign('documentID')->references('documentID')->on('VehicleDocument');
            });
        }
        
        if (Schema::hasTable('car_img')) {
            DB::statement('ALTER TABLE `car_img` MODIFY COLUMN `documentID` UNSIGNED INTEGER NULL;');
            
            Schema::table('car_img', function (Blueprint $table) {
                try {
                    $table->foreign('documentID')->references('documentID')->on('VehicleDocument');
                } catch (\Exception $e) {
                    // Ignore if foreign key already exists
                }
            });
        }
    }
};
