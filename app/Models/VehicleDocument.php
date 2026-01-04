<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleDocument extends Model
{
    protected $table = 'vehicledocument';
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
        'service_center',
        'description',
        'vehicleID',
        'staffID',
        'document_type',
        'leasing_document_url',
    ];

    protected function casts(): array
    {
        return [
            'mileage' => 'integer',
            'service_date' => 'date',
            'next_due_date' => 'date',
            'cost' => 'decimal:2',
        ];
    }

    /**
     * Get the vehicle for this document.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicleID', 'vehicleID');
    }

    /**
     * Get the staff member who handled this.
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staffID', 'staffID');
    }
}
