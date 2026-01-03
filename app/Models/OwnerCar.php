<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OwnerCar extends Model
{
    protected $table = 'OwnerCar';
    protected $primaryKey = 'ownerID';
    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'ic_no',
        'contact_number',
        'email',
        'bankname',
        'bank_acc_number',
        'registration_date',
    ];

    protected function casts(): array
    {
        return [
            'registration_date' => 'datetime',
        ];
    }

    /**
     * Get all vehicles owned by this owner.
     */
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'ownerID', 'ownerID');
    }
}

