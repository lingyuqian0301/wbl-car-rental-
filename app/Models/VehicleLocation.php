<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleLocation extends Model
{
    protected $fillable = [
        'vehicle_id',
        'latitude',
        'longitude',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'speed',
        'heading',
        'altitude',
        'battery_level',
        'is_moving',
        'location_method', // gps, network, etc.
        'accuracy',
        'location_provider'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'speed' => 'decimal:2',
        'heading' => 'decimal:2',
        'altitude' => 'decimal:2',
        'battery_level' => 'decimal:2',
        'is_moving' => 'boolean',
        'accuracy' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the vehicle that owns the location.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the location as a Google Maps URL.
     */
    public function getGoogleMapsUrlAttribute(): string
    {
        return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
    }

    /**
     * Get the last known location for a vehicle.
     */
    public static function getLastLocation($vehicleId)
    {
        return static::where('vehicle_id', $vehicleId)
            ->latest()
            ->first();
    }
}
