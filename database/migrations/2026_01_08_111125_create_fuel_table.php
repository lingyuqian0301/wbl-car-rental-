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
        Schema::create('fuel', function (Blueprint $table) {
            $table->id('fuelID');
            $table->unsignedInteger('vehicleID');
            $table->date('fuel_date');
            $table->decimal('cost', 10, 2);
            $table->string('receipt_img')->nullable();
            $table->unsignedInteger('handled_by')->nullable();
            $table->timestamps();

            $table->index('vehicleID');
            $table->index('handled_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fuel');
    }
};
