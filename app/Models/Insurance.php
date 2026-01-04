<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Insurance extends Model
{
    protected $table = 'insurance';
    protected $primaryKey = 'ins_ID';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'ins_company',
        'ins_coverageType',
        'ins_expirydate',
        'policyno',
        'documentID',
    ];

    protected function casts(): array
    {
        return [
            'ins_expirydate' => 'date',
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
