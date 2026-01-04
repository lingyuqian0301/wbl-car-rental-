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
        Schema::create('booking_read_status', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->foreignId('booking_id')->constrained('bookings'); // foreign key to bookings table
            $table->foreignId('user_id')->constrained('users');       // foreign key to users table
            $table->boolean('is_read')->default(false);               // to track read/unread
            $table->timestamps();                                     // created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_read_status');
    }
};
