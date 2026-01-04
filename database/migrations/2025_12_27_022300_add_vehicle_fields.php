<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('vehicles', function (Blueprint $table) {
            // Add new columns for vehicle tracking
            $table->integer('current_mileage')->nullable()->after('item_category_id');
            $table->date('last_service_date')->nullable()->after('current_mileage');
            $table->integer('next_service_mileage')->nullable()->after('last_service_date');
            
            // GPS tracking fields
            $table->decimal('gps_lat', 10, 8)->nullable()->after('next_service_mileage');
            $table->decimal('gps_lng', 11, 8)->nullable()->after('gps_lat');
            $table->timestamp('gps_last_updated_at')->nullable()->after('gps_lng');
            
            // Add index for better performance on common queries
            $table->index('status');
            $table->index('item_category_id');
        });
    }

    public function down()
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'current_mileage',
                'last_service_date',
                'next_service_mileage',
                'gps_lat',
                'gps_lng',
                'gps_last_updated_at'
            ]);
            
            $table->dropIndex(['status']);
            $table->dropIndex(['item_category_id']);
        });
    }
};
