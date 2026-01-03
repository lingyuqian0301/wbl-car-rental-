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
    protected $table = 'Payment';
    protected $primaryKey = 'paymentID';
    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'payment_bank_name',
        'payment_bank_account_no',
        'payment_date',
        'total_amount',
        'payment_status',
        'transaction_reference',
        'isPayment_complete',
        'payment_isVerify',
        'latest_Update_Date_Time',
        'bookingID',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'datetime',
            'total_amount' => 'decimal:2',
            'isPayment_complete' => 'boolean',
            'payment_isVerify' => 'boolean',
            'latest_Update_Date_Time' => 'datetime',
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
     * Get status (alias for payment_status).
     */
    public function getStatusAttribute()
    {
        return $this->payment_status;
    }

    /**
     * Get amount (alias for total_amount).
     */
    public function getAmountAttribute()
    {
        return $this->total_amount;
    }
}
