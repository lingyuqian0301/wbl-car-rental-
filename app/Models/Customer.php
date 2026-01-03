<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $table = 'Customer';
    protected $primaryKey = 'customerID';
    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'phone_number',
        'address',
        'customer_license',
        'emergency_contact',
        'booking_times',
        'userID',
    ];

    protected function casts(): array
    {
        return [
            'booking_times' => 'integer',
        ];
    }

    /**
     * Get the user that owns the customer.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'userID', 'userID');
    }

    /**
     * Get the local customer details.
     */
    public function local(): HasOne
    {
        return $this->hasOne(Local::class, 'customerID', 'customerID');
    }

    /**
     * Get the international customer details.
     */
    public function international(): HasOne
    {
        return $this->hasOne(International::class, 'customerID', 'customerID');
    }

    /**
     * Get the loyalty card for the customer.
     */
    public function loyaltyCard(): HasOne
    {
        return $this->hasOne(LoyaltyCard::class, 'customerID', 'customerID');
    }

    /**
     * Get the wallet account for the customer.
     */
    public function walletAccount(): HasOne
    {
        return $this->hasOne(WalletAccount::class, 'customerID', 'customerID');
    }

    /**
     * Get the bookings for the customer.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'customerID', 'customerID');
    }
}

