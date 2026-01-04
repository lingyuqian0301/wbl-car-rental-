<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Car extends Model
{
    protected $table = 'car';
    protected $primaryKey = 'vehicleID';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;
    
    protected $fillable = [
        'vehicleID',
        'ownerID',
        'seating_capacity',
        'transmission',
        'model',
        'car_type',
    ];

    protected function casts(): array
    {
        return [
            'seating_capacity' => 'integer',
        ];
    }
    
    /**
     * Get the vehicle details (common attributes).
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicleID', 'vehicleID');
    }
    
    /**
     * Get the owner of this car.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(OwnerCar::class, 'ownerID', 'ownerID');
    }
    
    /**
     * Get bookings for this car.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'vehicleID', 'vehicleID');
    }

    // Accessors to get attributes from vehicle table
    public function getVehicleBrandAttribute()
    {
        return $this->vehicle->vehicle_brand ?? null;
    }

    public function getVehicleModelAttribute()
    {
        return $this->vehicle->vehicle_model ?? null;
    }

    public function getPlateNumberAttribute()
    {
        return $this->vehicle->plate_number ?? null;
    }

    public function getAvailabilityStatusAttribute()
    {
        return $this->vehicle->availability_status ?? null;
    }

    public function getCreatedDateAttribute()
    {
        return $this->vehicle->created_date ?? null;
    }

    public function getManufacturingYearAttribute()
    {
        return $this->vehicle->manufacturing_year ?? null;
    }

    public function getColorAttribute()
    {
        return $this->vehicle->color ?? null;
    }

    public function getEngineCapacityAttribute()
    {
        return $this->vehicle->engineCapacity ?? null;
    }

    public function getVehicleTypeAttribute()
    {
        return $this->vehicle->vehicleType ?? null;
    }

    public function getRentalPriceAttribute()
    {
        return $this->vehicle->rental_price ?? null;
    }

    public function getIsActiveAttribute()
    {
        return $this->vehicle->isActive ?? null;
    }
}
