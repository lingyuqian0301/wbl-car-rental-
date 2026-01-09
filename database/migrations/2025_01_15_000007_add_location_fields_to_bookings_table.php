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
            $table->string('pickup_location')->nullable()->after('total_price');
            $table->string('return_location')->nullable()->after('pickup_location');
            $table->string('pickup_time')->nullable()->after('return_location');
            $table->string('return_time')->nullable()->after('pickup_time');
            $table->foreignId('confirmed_by')->nullable()->after('return_time')->constrained('users')->onDelete('set null');
            $table->timestamp('confirmed_at')->nullable()->after('confirmed_by');
            $table->foreignId('completed_by')->nullable()->after('confirmed_at')->constrained('users')->onDelete('set null');
            $table->timestamp('completed_at')->nullable()->after('completed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking', function (Blueprint $table) {
            $table->dropForeign(['confirmed_by']);
            $table->dropForeign(['completed_by']);
            $table->dropColumn(['pickup_location', 'return_location', 'pickup_time', 'return_time', 'confirmed_by', 'confirmed_at', 'completed_by', 'completed_at']);
        });
    }
};














