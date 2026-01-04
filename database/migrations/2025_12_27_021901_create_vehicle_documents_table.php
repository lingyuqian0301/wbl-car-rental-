<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('vehicle_documents')) {
            Schema::create('vehicle_documents', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('vehicleID'); // Use vehicleID to match cars/motorcycles tables
                $table->string('vehicle_type')->default('car'); // 'car', 'motorcycle', or 'vehicle'
                $table->string('type'); // insurance, grant, roadtax, etc.
                $table->string('document_number')->nullable();
                $table->date('issue_date')->nullable();
                $table->date('expiry_date')->nullable();
                $table->string('file_path');
                $table->text('notes')->nullable();
                $table->timestamps();
                
                // Note: Cannot use foreign key constraint as vehicleID may reference different tables
                $table->index('vehicleID');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('vehicle_documents');
    }
};
