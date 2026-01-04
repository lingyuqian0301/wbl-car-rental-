<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WalletAccount extends Model
{
    use HasFactory;

    protected $table = 'walletaccount';
    protected $primaryKey = 'walletAccountID';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'wallet_balance',
        'outstanding_amount',
        'wallet_status',
        'wallet_lastUpdate_Date_Time',
        'customerID',
    ];

    protected function casts(): array
    {
        return [
            'wallet_balance' => 'decimal:2',
            'outstanding_amount' => 'decimal:2',
            'wallet_lastUpdate_Date_Time' => 'datetime',
        ];
    }

    /**
     * Get the customer that owns this wallet account.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customerID', 'customerID');
    }

    /**
     * Get all transactions for this wallet account.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class, 'walletAccountID', 'walletAccountID');
    }
}
