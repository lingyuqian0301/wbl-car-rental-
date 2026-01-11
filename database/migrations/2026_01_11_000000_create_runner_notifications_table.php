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
        Schema::create('runner_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('runner_user_id');
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->string('type'); // new_pickup_task, new_return_task, task_updated, etc.
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index(['runner_user_id', 'is_read']);
            $table->index('booking_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('runner_notifications');
    }
};

