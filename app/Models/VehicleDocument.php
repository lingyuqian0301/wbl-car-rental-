<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class VehicleDocument extends Model
{
    protected $table = 'vehicledocument';
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
     * Get the road tax for this document.
     */
    public function roadTax(): HasOne
    {
        return $this->hasOne(Roadtax::class, 'documentID', 'documentID');
    }

    /**
     * Get the grant document.
     */
    public function grantDoc(): HasOne
    {
        return $this->hasOne(GrantDoc::class, 'documentID', 'documentID');
    }

    /**
     * Get the insurance.
     */
    public function insurance(): HasOne
    {
        return $this->hasOne(Insurance::class, 'documentID', 'documentID');
    }

    /**
     * Get the car images.
     */
    public function carImg(): HasOne
    {
        return $this->hasOne(Car_Img::class, 'documentID', 'documentID');
    }
}
