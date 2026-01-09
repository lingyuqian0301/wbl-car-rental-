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
            // Cancellation fields
            if (!Schema::hasColumn('booking', 'cancelled_by')) {
                $table->foreignId('cancelled_by')->nullable()->after('completed_at')->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('booking', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');
            }
            if (!Schema::hasColumn('booking', 'cancellation_reason')) {
                $table->text('cancellation_reason')->nullable()->after('cancelled_at');
            }
            if (!Schema::hasColumn('booking', 'refund_status')) {
                $table->enum('refund_status', ['Pending', 'Processing', 'Completed', 'Rejected'])->nullable()->after('cancellation_reason');
            }
            if (!Schema::hasColumn('booking', 'refund_reason')) {
                $table->text('refund_reason')->nullable()->after('refund_status');
            }
            if (!Schema::hasColumn('booking', 'refund_processed_by')) {
                $table->foreignId('refund_processed_by')->nullable()->after('refund_reason')->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('booking', 'refund_processed_at')) {
                $table->timestamp('refund_processed_at')->nullable()->after('refund_processed_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking', function (Blueprint $table) {
            if (Schema::hasColumn('booking', 'refund_processed_at')) {
                $table->dropColumn('refund_processed_at');
            }
            if (Schema::hasColumn('booking', 'refund_processed_by')) {
                $table->dropForeign(['refund_processed_by']);
                $table->dropColumn('refund_processed_by');
            }
            if (Schema::hasColumn('booking', 'refund_reason')) {
                $table->dropColumn('refund_reason');
            }
            if (Schema::hasColumn('booking', 'refund_status')) {
                $table->dropColumn('refund_status');
            }
            if (Schema::hasColumn('booking', 'cancellation_reason')) {
                $table->dropColumn('cancellation_reason');
            }
            if (Schema::hasColumn('booking', 'cancelled_at')) {
                $table->dropColumn('cancelled_at');
            }
            if (Schema::hasColumn('booking', 'cancelled_by')) {
                $table->dropForeign(['cancelled_by']);
                $table->dropColumn('cancelled_by');
            }
        });
    }
};











