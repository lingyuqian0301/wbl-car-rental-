<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
     * Get the person details for this owner (to get name).
     */
    public function personDetails(): BelongsTo
    {
        return $this->belongsTo(PersonDetails::class, 'ic_no', 'ic_no');
    }

    /**
     * Get the cars owned by this owner.
     */
    public function cars()
    {
        return $this->hasMany(Car::class, 'ownerID', 'ownerID');
    }

    /**
     * Get owner name from PersonDetails.
     */
    public function getOwnerNameAttribute()
    {
        return $this->personDetails->fullname ?? 'N/A';
    }
}
