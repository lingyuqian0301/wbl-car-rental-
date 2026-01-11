<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payment';
    protected $primaryKey = 'paymentID';
    public $timestamps = false; // We use custom timestamp columns

    protected $fillable = [
        'bookingID',
        'total_amount',
        'payment_bank_name',
        'payment_bank_account_no',
        'transaction_reference',
        'payment_status',       // e.g., 'Pending', 'Verified'
        'payment_date',
        'isPayment_complete',   // boolean
        'payment_isVerify',     // boolean
        'latest_Update_Date_Time',
        'proof_of_payment',     // image path
        'verified_by',          // ID of admin/staff who verified it
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'latest_Update_Date_Time' => 'datetime',
        'isPayment_complete' => 'boolean',
        'payment_isVerify' => 'boolean',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Get the booking associated with the payment.
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'bookingID', 'bookingID');
    }

    /**
     * Get the wallet transactions associated with the payment.
     */
    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class, 'paymentID', 'paymentID');
    }
}