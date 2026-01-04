<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('vehicle_photos')) {
            Schema::create('vehicle_photos', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('vehicleID'); // Use vehicleID to match cars/motorcycles tables
                $table->string('vehicle_type')->default('car'); // 'car', 'motorcycle', or 'vehicle'
                $table->string('path');
                $table->string('caption')->nullable();
                $table->boolean('is_primary')->default(false);
                $table->integer('display_order')->default(0);
                $table->timestamps();
                
                // Note: Cannot use foreign key constraint as vehicleID may reference different tables
                $table->index('vehicleID');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('vehicle_photos');
    }
};
