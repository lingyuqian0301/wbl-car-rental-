<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdditionalCharge extends Model
{
    protected $table = 'additionalcharges';
    protected $primaryKey = 'chargeID';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'addOns_charge',
        'late_return_fee',
        'damage_fee',
        'total_extra_charge',
        'bookingID',
    ];

    protected function casts(): array
    {
        return [
            'addOns_charge' => 'decimal:2',
            'late_return_fee' => 'decimal:2',
            'damage_fee' => 'decimal:2',
            'total_extra_charge' => 'decimal:2',
        ];
    }

    /**
     * Get the booking that owns this additional charge.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'bookingID', 'bookingID');
    }
}
