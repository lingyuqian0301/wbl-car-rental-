<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Car_Img extends Model
{
    protected $table = 'Car_Img';
    protected $primaryKey = 'imgID';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'imageType',
        'img_description',
        'documentID',
    ];

    /**
     * Get the vehicle document.
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(VehicleDocument::class, 'documentID', 'documentID');
    }
}
