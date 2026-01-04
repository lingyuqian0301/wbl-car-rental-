<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $table = 'payment';
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
        'verify_by',
        'bookingID',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'datetime',
            'total_amount' => 'decimal:2',
            'latest_Update_Date_Time' => 'datetime',
            'isPayment_complete' => 'boolean',
            'payment_isVerify' => 'boolean',
        ];
    }

    /**
     * Get the booking that owns this payment.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'bookingID', 'bookingID');
    }
}
