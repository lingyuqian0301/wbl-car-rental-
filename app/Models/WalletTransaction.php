<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $table = 'wallettransaction';
    protected $primaryKey = 'transactionID';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'walletAccountID',
        'paymentID',
        'transaction_type',
        'amount',
        'transaction_date',
        'description',
        'reference_type',
        'reference_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    /**
     * Get the wallet account that owns this transaction.
     */
    public function walletAccount(): BelongsTo
    {
        return $this->belongsTo(WalletAccount::class, 'walletAccountID', 'walletAccountID');
    }
}

