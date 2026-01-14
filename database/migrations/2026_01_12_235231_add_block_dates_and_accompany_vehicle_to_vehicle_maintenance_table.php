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
            if (!Schema::hasColumn('VehicleMaintenance', 'block_start_date')) {
                $table->date('block_start_date')->nullable()->after('maintenance_img');
            }
            if (!Schema::hasColumn('VehicleMaintenance', 'block_end_date')) {
                $table->date('block_end_date')->nullable()->after('block_start_date');
            }
            if (!Schema::hasColumn('VehicleMaintenance', 'accompany_vehicleID')) {
                $table->unsignedInteger('accompany_vehicleID')->nullable()->after('block_end_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('VehicleMaintenance', function (Blueprint $table) {
            if (Schema::hasColumn('VehicleMaintenance', 'accompany_vehicleID')) {
                $table->dropColumn('accompany_vehicleID');
            }
            if (Schema::hasColumn('VehicleMaintenance', 'block_end_date')) {
                $table->dropColumn('block_end_date');
            }
            if (Schema::hasColumn('VehicleMaintenance', 'block_start_date')) {
                $table->dropColumn('block_start_date');
            }
        });
    }
};
