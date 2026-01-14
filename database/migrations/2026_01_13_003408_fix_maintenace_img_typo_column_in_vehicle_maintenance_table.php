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
        Schema::table('VehicleMaintenance', function (Blueprint $table) {
            // Fix the typo column maintenace_img to be nullable (it has a typo - should be maintenance_img)
            // This column shouldn't exist but we'll make it nullable to prevent errors
            if (Schema::hasColumn('VehicleMaintenance', 'maintenace_img')) {
                \Illuminate\Support\Facades\DB::statement('ALTER TABLE `VehicleMaintenance` MODIFY COLUMN `maintenace_img` VARCHAR(255) NULL DEFAULT NULL');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Don't revert - this is a fix for a typo column
    }
};
