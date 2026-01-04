<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('maintenance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->string('service_type'); // battery, meter, general, etc.
            $table->date('service_date');
            $table->decimal('cost', 10, 2);
            $table->text('description');
            $table->string('service_provider')->nullable();
            $table->integer('mileage')->nullable();
            $table->date('next_service_date')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('maintenance_records');
    }
};
