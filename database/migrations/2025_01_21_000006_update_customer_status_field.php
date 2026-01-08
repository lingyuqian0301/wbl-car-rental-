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
        Schema::table('customer', function (Blueprint $table) {
            // Remove is_blacklisted fields if they exist
            if (Schema::hasColumn('customer', 'blacklisted_at')) {
                $table->dropColumn('blacklisted_at');
            }
            if (Schema::hasColumn('customer', 'blacklist_reason')) {
                $table->dropColumn('blacklist_reason');
            }
            if (Schema::hasColumn('customer', 'is_blacklisted')) {
                $table->dropColumn('is_blacklisted');
            }
            
            // Add customer_status if it doesn't exist
            if (!Schema::hasColumn('customer', 'customer_status')) {
                $table->enum('customer_status', ['active', 'blacklist', 'deleted'])->default('active')->after('customer_license');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer', function (Blueprint $table) {
            if (Schema::hasColumn('customer', 'customer_status')) {
                $table->dropColumn('customer_status');
            }
            
            // Restore is_blacklisted fields if needed
            if (!Schema::hasColumn('customer', 'is_blacklisted')) {
                $table->boolean('is_blacklisted')->default(false)->after('customer_license');
            }
            if (!Schema::hasColumn('customer', 'blacklist_reason')) {
                $table->text('blacklist_reason')->nullable()->after('is_blacklisted');
            }
            if (!Schema::hasColumn('customer', 'blacklisted_at')) {
                $table->timestamp('blacklisted_at')->nullable()->after('blacklist_reason');
            }
        });
    }
};









