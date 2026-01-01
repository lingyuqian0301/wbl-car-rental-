<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    // 1. Link to the 'cars' table
    protected $table = 'cars';

    // 2. Define the Primary Key
    protected $primaryKey = 'vehicleID';

    // 3. Key Settings
    public $incrementing = true;
    protected $keyType = 'int';

    // 4. Mass Assignable Fields
    protected $fillable = [
        'brand',
        'model',
        'registration_number',
        'daily_rate',
        'status',
        'description',
        'item_category_id',
    ];

    protected function casts(): array
    {
        return [
            'daily_rate' => 'decimal:2',
        ];
    }

    // --- ACCESSORS (The Fix for "N/A") ---

    // This allows the View to use $vehicle->vehicle_brand
    public function getVehicleBrandAttribute()
    {
        return $this->attributes['brand'] ?? $this->attributes['vehicle_brand'] ?? 'Unknown Brand';
    }

    // This allows the View to use $vehicle->vehicle_model
    public function getVehicleModelAttribute()
    {
        return $this->attributes['model'] ?? $this->attributes['vehicle_model'] ?? 'Unknown Model';
    }

    // This allows the View to use $vehicle->plate_number
    public function getPlateNumberAttribute()
    {
        return $this->attributes['registration_number'] ?? $this->attributes['plate_number'] ?? 'No Plate';
    }

    // This allows the View to use $vehicle->full_model
    public function getFullModelAttribute()
    {
        $brand = $this->vehicle_brand ?? $this->brand ?? 'Unknown';
        $model = $this->vehicle_model ?? $this->model ?? 'Unknown';
        return $brand . ' ' . $model;
    }

    // --- RELATIONSHIPS ---

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'vehicleID', 'vehicleID');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ItemCategory::class, 'item_category_id');
    }
}
