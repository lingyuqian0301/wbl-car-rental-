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
            if (!Schema::hasColumn('booking', 'cancellation_reject_reason')) {
                $table->text('cancellation_reject_reason')->nullable()->after('deposit_customer_choice');
            }
            if (!Schema::hasColumn('booking', 'cancellation_receipt')) {
                $table->string('cancellation_receipt', 500)->nullable()->after('cancellation_reject_reason');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking', function (Blueprint $table) {
            if (Schema::hasColumn('booking', 'cancellation_reject_reason')) {
                $table->dropColumn('cancellation_reject_reason');
            }
            if (Schema::hasColumn('booking', 'cancellation_receipt')) {
                $table->dropColumn('cancellation_receipt');
            }
        });
    }
};