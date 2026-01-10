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
        Schema::table('booking_read_status', function (Blueprint $table) {
            if (!Schema::hasColumn('booking_read_status', 'date_type')) {
                $table->string('date_type', 20)->default('pickup')->after('booking_id');
            }
        });
        
        // Add unique constraint in separate call to avoid issues
        Schema::table('booking_read_status', function (Blueprint $table) {
            // Try to add the new unique constraint
            try {
                $table->unique(['booking_id', 'user_id', 'date_type'], 'booking_read_status_unique');
            } catch (\Exception $e) {
                // Constraint may already exist
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_read_status', function (Blueprint $table) {
            $table->dropColumn('date_type');
        });
    }
};
