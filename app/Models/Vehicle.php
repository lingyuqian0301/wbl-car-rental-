<?php

namespace App\Models;
use App\Models\Car;
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
     * Get car images for this vehicle.
     * Note: Only works if vehicleID column exists in Car_Img table (after migration)
     */
    public function carImages(): HasMany
    {
        // Only define relationship if vehicleID column exists
        if (\Illuminate\Support\Facades\Schema::hasColumn('Car_Img', 'vehicleID') || 
            \Illuminate\Support\Facades\Schema::hasColumn('car_img', 'vehicleID')) {
            $tableName = \Illuminate\Support\Facades\Schema::hasTable('Car_Img') ? 'Car_Img' : 'car_img';
            return $this->hasMany(\App\Models\CarImg::class, 'vehicleID', 'vehicleID');
        }
        
        // Return empty relationship if column doesn't exist
        return $this->hasMany(\App\Models\CarImg::class, 'vehicleID', 'vehicleID')->whereRaw('1 = 0');
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
     * Get the maintenance records for this vehicle.
     */
    public function maintenances(): HasMany
    {
        return $this->hasMany(VehicleMaintenance::class, 'vehicleID', 'vehicleID');
    }

    /**
     * Get the documents for this vehicle.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(VehicleDocument::class, 'vehicleID', 'vehicleID');
    }

    /**
     * Get the fuel records for this vehicle.
     */
    public function fuels(): HasMany
    {
        return $this->hasMany(Fuel::class, 'vehicleID', 'vehicleID');
    }
}