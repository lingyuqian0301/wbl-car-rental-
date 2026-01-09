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
            // Check if start_date doesn't exist but rental_start_date does
            if (!Schema::hasColumn('booking', 'start_date') && Schema::hasColumn('booking', 'rental_start_date')) {
                $table->renameColumn('rental_start_date', 'start_date');
            }
            // Check if end_date doesn't exist but rental_end_date does
            if (!Schema::hasColumn('booking', 'end_date') && Schema::hasColumn('booking', 'rental_end_date')) {
                $table->renameColumn('rental_end_date', 'end_date');
            }
            // Check if id doesn't exist but bookingID does (set as primary key)
            if (!Schema::hasColumn('booking', 'id') && Schema::hasColumn('booking', 'bookingID')) {
                // bookingID should already be primary, but ensure it's auto-increment
                // Note: Can't rename primary key easily, so we'll handle this in model
            }
            // If both id and bookingID exist, keep id as primary and make bookingID nullable/unique
            if (Schema::hasColumn('booking', 'id') && Schema::hasColumn('booking', 'bookingID')) {
                // Keep both, id is primary
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking', function (Blueprint $table) {
            if (Schema::hasColumn('booking', 'start_date') && !Schema::hasColumn('booking', 'rental_start_date')) {
                $table->renameColumn('start_date', 'rental_start_date');
            }
            if (Schema::hasColumn('booking', 'end_date') && !Schema::hasColumn('booking', 'rental_end_date')) {
                $table->renameColumn('end_date', 'rental_end_date');
            }
        });
    }
};












