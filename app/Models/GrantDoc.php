<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrantDoc extends Model
{
    protected $table = 'GrantDoc';
    protected $primaryKey = 'grantID';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'grantNo',
        'grantType',
        'grant_expirydate',
        'documentID',
    ];

    protected function casts(): array
    {
        return [
            'grant_expirydate' => 'date',
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
