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
        if (!Schema::hasTable('voucher_usage')) {
        Schema::create('voucher_usage', function (Blueprint $table) {
                $table->id('usageID');
                $table->unsignedInteger('voucherID');
                $table->unsignedInteger('customerID');
                $table->unsignedInteger('bookingID')->nullable();
                $table->timestamp('used_at')->useCurrent();
            $table->timestamps();
                
                $table->index('voucherID');
                $table->index('customerID');
                $table->index('bookingID');
        });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voucher_usage');
    }
};
