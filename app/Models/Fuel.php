<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fuel extends Model
{
    protected $table = 'fuel';
    protected $primaryKey = 'fuelID';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'vehicleID',
        'fuel_date',
        'service_type',
        'cost',
        'receipt_img',
        'handled_by',
    ];

    protected function casts(): array
    {
        return [
            'fuel_date' => 'date',
            'cost' => 'decimal:2',
        ];
    }

    /**
     * Get the vehicle for this fuel record.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicleID', 'vehicleID');
    }

    /**
     * Get the user (staff/admin) who handled the fuel.
     */
    public function handledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by', 'userID');
    }
}
