<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarImg extends Model
{
    protected $table = 'Car_Img'; // Match actual database table name
    protected $primaryKey = 'imgID';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'imageType',
        'img_description',
        'documentID', // Now stores Google Drive URL as text/string
        'vehicleID', // Link directly to vehicle
    ];

    /**
     * Get the vehicle this image belongs to (if vehicleID exists)
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicleID', 'vehicleID');
    }
}
