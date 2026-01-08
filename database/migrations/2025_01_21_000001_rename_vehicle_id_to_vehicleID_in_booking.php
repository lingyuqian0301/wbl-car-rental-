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
        Schema::table('booking', function (Blueprint $table) {
            // Check if vehicle_id exists and vehicleID doesn't
            if (Schema::hasColumn('booking', 'vehicle_id') && !Schema::hasColumn('booking', 'vehicleID')) {
                $table->renameColumn('vehicle_id', 'vehicleID');
            }
            // If both exist, drop vehicle_id and keep vehicleID
            elseif (Schema::hasColumn('booking', 'vehicle_id') && Schema::hasColumn('booking', 'vehicleID')) {
                $table->dropColumn('vehicle_id');
            }
            // If vehicleID doesn't exist, add it
            elseif (!Schema::hasColumn('booking', 'vehicleID')) {
                $table->unsignedBigInteger('vehicleID')->after('user_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking', function (Blueprint $table) {
            if (Schema::hasColumn('booking', 'vehicleID') && !Schema::hasColumn('booking', 'vehicle_id')) {
                $table->renameColumn('vehicleID', 'vehicle_id');
            }
        });
    }
};










