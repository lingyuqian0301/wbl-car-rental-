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
        Schema::table('users', function (Blueprint $table) {
            // Customer account fields for refunds
            if (!Schema::hasColumn('users', 'account_no')) {
                $table->string('account_no')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'account_type')) {
                $table->string('account_type')->nullable()->after('account_no'); // e.g., 'Bank Account', 'E-Wallet', etc.
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'account_type')) {
                $table->dropColumn('account_type');
            }
            if (Schema::hasColumn('users', 'account_no')) {
                $table->dropColumn('account_no');
            }
        });
    }
};











