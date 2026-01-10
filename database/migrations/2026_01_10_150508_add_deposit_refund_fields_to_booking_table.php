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
            if (!Schema::hasColumn('booking', 'deposit_refund_status')) {
                $table->enum('deposit_refund_status', ['pending', 'refunded'])->nullable()->after('deposit_amount');
            }
            if (!Schema::hasColumn('booking', 'deposit_handled_by')) {
                $table->unsignedBigInteger('deposit_handled_by')->nullable()->after('deposit_refund_status');
            }
            if (!Schema::hasColumn('booking', 'deposit_fine_amount')) {
                $table->decimal('deposit_fine_amount', 10, 2)->nullable()->after('deposit_handled_by');
            }
            if (!Schema::hasColumn('booking', 'deposit_refund_amount')) {
                $table->decimal('deposit_refund_amount', 10, 2)->nullable()->after('deposit_fine_amount');
            }
            if (!Schema::hasColumn('booking', 'deposit_customer_choice')) {
                $table->enum('deposit_customer_choice', ['hold', 'refund'])->nullable()->after('deposit_refund_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking', function (Blueprint $table) {
            if (Schema::hasColumn('booking', 'deposit_refund_status')) {
                $table->dropColumn('deposit_refund_status');
            }
            if (Schema::hasColumn('booking', 'deposit_handled_by')) {
                $table->dropColumn('deposit_handled_by');
            }
            if (Schema::hasColumn('booking', 'deposit_fine_amount')) {
                $table->dropColumn('deposit_fine_amount');
            }
            if (Schema::hasColumn('booking', 'deposit_refund_amount')) {
                $table->dropColumn('deposit_refund_amount');
            }
            if (Schema::hasColumn('booking', 'deposit_customer_choice')) {
                $table->dropColumn('deposit_customer_choice');
            }
        });
    }
};
