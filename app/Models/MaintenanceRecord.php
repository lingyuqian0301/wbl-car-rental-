<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceRecord extends Model
{
    protected $table = 'maintenance_records';
    
    protected $fillable = [
        'vehicle_id',
        'service_type',
        'service_date',
        'cost',
        'description',
        'service_provider',
        'mileage',
        'next_service_date',
    ];

    protected function casts(): array
    {
        return [
            'cost' => 'decimal:2',
            'service_date' => 'date',
            'next_service_date' => 'date',
            'mileage' => 'integer',
        ];
    }
}
