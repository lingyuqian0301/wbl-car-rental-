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
        Schema::table('payment', function (Blueprint $table) {
            // Add verified_by column to store the user ID of the staff who verified the payment
            $table->unsignedBigInteger('verified_by')->nullable()->after('payment_isVerify');
            
            // Add foreign key constraint (optional, can be commented out if user table has different structure)
            // $table->foreign('verified_by')->references('userID')->on('user')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment', function (Blueprint $table) {
            $table->dropColumn('verified_by');
        });
    }
};
