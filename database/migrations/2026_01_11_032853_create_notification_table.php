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
        // Skip if table already exists
        if (Schema::hasTable('notification')) {
            return;
        }
        
        Schema::create('notification', function (Blueprint $table) {
            $table->id();
            $table->string('type', 100); // new_booking, new_cancellation, new_payment, booking_date_changed, etc.
            $table->string('notifiable_type', 50)->nullable(); // admin, user
            $table->unsignedBigInteger('notifiable_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable(); // Related user
            $table->unsignedBigInteger('booking_id')->nullable(); // Related booking
            $table->unsignedBigInteger('payment_id')->nullable(); // Related payment
            $table->text('message');
            $table->json('data')->nullable(); // Additional data
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('type');
            $table->index('notifiable_type');
            $table->index('is_read');
            $table->index('created_at');
            $table->index('user_id');
            $table->index('booking_id');
            $table->index('payment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification');
    }
    
    /**
     * Check if this migration should be skipped.
     */
    public function shouldRun(): bool
    {
        return !Schema::hasTable('notification');
    }
};
