<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    protected $table = 'vehicle';
    protected $primaryKey = 'vehicleID';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'vehicleID',
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
            'created_date' => 'date',
            'manufacturing_year' => 'integer',
            'engineCapacity' => 'decimal:2',
            'rental_price' => 'decimal:2',
            'isActive' => 'boolean',
        ];
    }

    /**
     * Get the owner of this vehicle.
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
     * Get the car details for this vehicle (if it's a car).
     */
    public function car()
    {
        return $this->hasOne(Car::class, 'vehicleID', 'vehicleID');
    }

    /**
     * Get the motorcycle details for this vehicle (if it's a motorcycle).
     */
    public function motorcycle()
    {
        return $this->hasOne(Motorcycle::class, 'vehicleID', 'vehicleID');
    }

    /**
     * Get the vehicle documents.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(VehicleDocument::class, 'vehicleID', 'vehicleID');
    }

    /**
     * Get the vehicle maintenance records.
     */
    public function maintenanceRecords(): HasMany
    {
        return $this->hasMany(VehicleMaintenance::class, 'vehicleID', 'vehicleID');
    }

    /**
     * Get the browse history for this vehicle.
     */
    public function browseHistory(): HasMany
    {
        return $this->hasMany(BrowseHistory::class, 'vehicleID', 'vehicleID');
    }

    /**
     * Get vehicle_type attribute (alias for vehicleType).
     */
    public function getVehicleTypeAttribute()
    {
        return $this->attributes['vehicleType'] ?? null;
    }

    /**
     * Get transmission from car relationship.
     */
    public function getTransmissionAttribute()
    {
        return $this->car?->transmission ?? 'N/A';
    }

    /**
     * Get seating capacity from car relationship.
     */
    public function getSeatingCapacityAttribute()
    {
        return $this->car?->seating_capacity ?? 'N/A';
    }

    /**
     * Get car type from car relationship.
     */
    public function getTypeAttribute()
    {
        return $this->car?->car_type ?? $this->attributes['vehicleType'] ?? 'N/A';
    }

    /**
     * Get full model name (brand + model).
     */
    public function getFullModelAttribute()
    {
        return trim($this->vehicle_brand . ' ' . $this->vehicle_model);
    }

    /**
     * Get the route key for the model (for route model binding).
     */
    public function getRouteKeyName(): string
    {
        return 'vehicleID';
    }
}
