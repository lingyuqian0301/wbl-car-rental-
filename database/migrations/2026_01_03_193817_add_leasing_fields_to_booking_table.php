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
            // Note: staff_served is handled via booking_served_by table
            // But we can add a direct reference if needed for convenience
            if (!Schema::hasColumn('booking', 'staff_served')) {
                $table->unsignedBigInteger('staff_served')->nullable()->after('booking_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking', function (Blueprint $table) {
            if (Schema::hasColumn('booking', 'staff_served')) {
                $table->dropColumn('staff_served');
            }
        });
    }
};
