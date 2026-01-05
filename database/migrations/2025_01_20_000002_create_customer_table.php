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
        Schema::create('customer', function (Blueprint $table) {
            $table->id('customerID');
            $table->unsignedBigInteger('userID')->nullable();
            $table->string('matric_number')->nullable();
            $table->string('fullname');
            $table->string('ic_number')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('college')->nullable();
            $table->string('faculty')->nullable();
            $table->string('customer_type')->nullable();
            $table->date('registration_date')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->string('country')->nullable();
            $table->string('customer_license')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer');
    }
};







