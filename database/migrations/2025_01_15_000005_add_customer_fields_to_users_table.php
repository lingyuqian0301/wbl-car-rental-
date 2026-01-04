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
            $table->string('phone')->nullable()->after('email');
            $table->text('address')->nullable()->after('phone');
            $table->string('faculty')->nullable()->after('address');
            $table->string('college')->nullable()->after('faculty');
            $table->boolean('is_blacklisted')->default(false)->after('college');
            $table->text('blacklist_reason')->nullable()->after('is_blacklisted');
            $table->timestamp('blacklisted_at')->nullable()->after('blacklist_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'address', 'faculty', 'college', 'is_blacklisted', 'blacklist_reason', 'blacklisted_at']);
        });
    }
};










