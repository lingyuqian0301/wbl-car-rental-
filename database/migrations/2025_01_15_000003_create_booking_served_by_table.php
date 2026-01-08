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
        if (!Schema::hasTable('booking_served_by')) {
            Schema::create('booking_served_by', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('booking_id');
                $table->foreignId('served_by_user_id')->constrained('users')->onDelete('cascade');
                $table->timestamp('served_at');
                $table->text('notes')->nullable();
                $table->timestamps();
                
                // Foreign key constraint - use bookingID if it exists, otherwise id
                if (Schema::hasColumn('booking', 'bookingID')) {
                    $table->foreign('booking_id')->references('bookingID')->on('booking')->onDelete('cascade');
                } else {
                    $table->foreign('booking_id')->references('id')->on('booking')->onDelete('cascade');
                }
                
                $table->unique(['booking_id', 'served_by_user_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_served_by');
    }
};













