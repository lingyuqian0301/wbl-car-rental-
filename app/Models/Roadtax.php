<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Roadtax extends Model
{
    protected $table = 'Roadtax';
    protected $primaryKey = 'roadtax_ID';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'roadtax_certificationNo',
        'roadtax_expirydate',
        'documentID',
    ];

    protected function casts(): array
    {
        return [
            'roadtax_expirydate' => 'date',
        ];
    }

    /**
     * Get the vehicle document.
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(VehicleDocument::class, 'documentID', 'documentID');
    }
}
