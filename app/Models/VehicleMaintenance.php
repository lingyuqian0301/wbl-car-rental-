<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleMaintenance extends Model
{
    protected $table = 'VehicleMaintenance';
    protected $primaryKey = 'maintenanceID';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'mileage',
        'service_date',
        'service_type',
        'next_due_date',
        'cost',
        'commission_amount',
        'service_center',
        'description',
        'vehicleID',
        'staffID',
        'maintenance_img',
        'block_start_date',
        'block_end_date',
        'accompany_vehicleID',
    ];

    protected function casts(): array
    {
        return [
            'service_date' => 'date',
            'next_due_date' => 'date',
            'block_start_date' => 'date',
            'block_end_date' => 'date',
            'cost' => 'decimal:2',
            'commission_amount' => 'decimal:2',
        ];
    }

    /**
     * Get the accompany vehicle.
     */
    public function accompanyVehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'accompany_vehicleID', 'vehicleID');
    }

    /**
     * Get the vehicle for this maintenance.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicleID', 'vehicleID');
    }

    /**
     * Get the staff who performed the maintenance.
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staffID', 'staffID');
    }

    /**
     * Get the user (staff/admin) who handled the maintenance.
     */
    public function handledByUser(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'staffID', 'userID');
    }
}
