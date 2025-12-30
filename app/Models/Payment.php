<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table = "payment";
    protected $primaryKey = 'paymentID';
    public $incrementing = true;
    protected $keyType = 'int';
    
    protected $fillable = [
        'bookingID',
        'amount',
        'payment_type',
        'payment_purpose',
        'payment_date',
        'receiptURL',
        'status',
        'deposit_returned',
        'keep_deposit',
        'transaction_reference',
        'refund_amount',
        'refund_date',
        'deposit_bank_name',
        'deposit_bank_number',
        'isPayment_complete',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'date',
            'deposit_returned' => 'boolean',
            'keep_deposit' => 'boolean',
        ];
    }

    /**
     * Get the booking that owns the payment.
     */
    public function booking(): BelongsTo
    {
        // payment table uses bookingID (singular booking table)
        return $this->belongsTo(Booking::class, 'bookingID', 'bookingID');
    }

    /**
     * Get the user who verified the payment.
     * Note: payment table doesn't have verified_by, but keeping for compatibility
     */
    public function verifier(): BelongsTo
    {
        // payment table doesn't have verified_by field, return null relationship
        return $this->belongsTo(User::class, 'verified_by')->whereRaw('1 = 0');
    }
}
