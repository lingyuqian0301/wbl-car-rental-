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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'new_booking', 'payment_received', 'new_customer'
            $table->morphs('notifiable'); // polymorphic relation
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade'); // who triggered it
            $table->foreignId('booking_id')->nullable()->constrained('booking')->onDelete('cascade');
            $table->foreignId('payment_id')->nullable()->constrained('payment')->onDelete('cascade');
            $table->text('message');
            $table->json('data')->nullable(); // additional data
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            $table->index(['notifiable_type', 'notifiable_id', 'is_read']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};













