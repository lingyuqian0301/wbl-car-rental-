<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class VehicleDocument extends Model
{
    protected $table = 'VehicleDocument';
    protected $primaryKey = 'documentID';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'documentID',
        'upload_date',
        'verification_date',
        'fileURL',
        'vehicleID',
        'document_type',
    ];

    protected function casts(): array
    {
        return [
            'upload_date' => 'date',
            'verification_date' => 'date',
        ];
    }

    /**
     * Get the vehicle for this document.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicleID', 'vehicleID');
    }
}