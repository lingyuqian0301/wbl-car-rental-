<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Car extends Model
{
    protected $table = 'Car';
    protected $primaryKey = 'vehicleID';
    public $incrementing = false;
    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'vehicleID',
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
     * Get the vehicle.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicleID', 'vehicleID');
    }
}

