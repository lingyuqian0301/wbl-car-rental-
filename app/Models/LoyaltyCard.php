<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyCard extends Model
{
    protected $table = 'loyaltycard';
    protected $primaryKey = 'loyaltyCardID';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'loyalty_last_updated',
        'total_stamps',
        'customerID',
    ];

    protected function casts(): array
    {
        return [
            'loyalty_last_updated' => 'datetime',
            'total_stamps' => 'integer',
        ];
    }

    /**
     * Get the customer that owns this loyalty card.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customerID', 'customerID');
    }
}
