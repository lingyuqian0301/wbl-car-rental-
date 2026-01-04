<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OwnerCar extends Model
{
    protected $table = 'ownercar';
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
        'leasing_due_date',
        'leasing_price',
        'isActive',
        'leasing_end_month',
        'leasing_end_year',
    ];

    protected function casts(): array
    {
        return [
            'registration_date' => 'date',
            'leasing_due_date' => 'date',
            'leasing_price' => 'decimal:2',
            'isActive' => 'boolean',
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
