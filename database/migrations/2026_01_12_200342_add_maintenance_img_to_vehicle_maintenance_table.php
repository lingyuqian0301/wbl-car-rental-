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
            if (!Schema::hasColumn('VehicleMaintenance', 'maintenance_img')) {
                $table->string('maintenance_img', 500)->nullable()->after('service_center');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('VehicleMaintenance', function (Blueprint $table) {
            if (Schema::hasColumn('VehicleMaintenance', 'maintenance_img')) {
                $table->dropColumn('maintenance_img');
            }
        });
    }
};
