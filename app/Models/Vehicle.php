<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    protected $table = 'Vehicle';
    protected $primaryKey = 'vehicleID';
    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'plate_number',
        'availability_status',
        'created_date',
        'vehicle_brand',
        'vehicle_model',
        'manufacturing_year',
        'color',
        'engineCapacity',
        'vehicleType',
        'rental_price',
        'isActive',
        'ownerID',
    ];

    protected function casts(): array
    {
        return [
            'created_date' => 'datetime',
            'manufacturing_year' => 'integer',
            'engineCapacity' => 'decimal:2',
            'rental_price' => 'decimal:2',
            'isActive' => 'boolean',
        ];
    }

    // --- ACCESSORS ---

    // This allows the View to use $vehicle->full_model
    public function getFullModelAttribute()
    {
        $brand = $this->vehicle_brand ?? 'Unknown';
        $model = $this->vehicle_model ?? 'Unknown';
        return $brand . ' ' . $model;
    }

    // --- RELATIONSHIPS ---

    /**
     * Get the owner of the vehicle.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(OwnerCar::class, 'ownerID', 'ownerID');
    }

    /**
     * Get the bookings for the vehicle.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'vehicleID', 'vehicleID');
    }

    /**
     * Get the car details.
     */
    public function car(): HasOne
    {
        return $this->hasOne(Car::class, 'vehicleID', 'vehicleID');
    }

    /**
     * Get the motorcycle details.
     */
    public function motorcycle(): HasOne
    {
        return $this->hasOne(Motorcycle::class, 'vehicleID', 'vehicleID');
    }
}
