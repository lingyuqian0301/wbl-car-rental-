<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleDocument extends Model
{
    protected $table = 'VehicleDocument';
    protected $primaryKey = 'documentID';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'upload_date',
        'verification_date',
        'fileURL',
        'vehicleID',
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

    /**
     * Get the insurance documents.
     */
    public function insurance(): HasMany
    {
        return $this->hasMany(Insurance::class, 'documentID', 'documentID');
    }

    /**
     * Get the road tax documents.
     */
    public function roadtax(): HasMany
    {
        return $this->hasMany(Roadtax::class, 'documentID', 'documentID');
    }

    /**
     * Get the grant documents.
     */
    public function grants(): HasMany
    {
        return $this->hasMany(GrantDoc::class, 'documentID', 'documentID');
    }

    /**
     * Get the car images.
     */
    public function images(): HasMany
    {
        return $this->hasMany(Car_Img::class, 'documentID', 'documentID');
    }
}
