<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoucherUsage extends Model
{
    protected $table = 'voucher_usage';
    protected $primaryKey = 'usageID';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'voucherID',
        'customerID',
        'bookingID',
        'used_at',
    ];

    protected function casts(): array
    {
        return [
            'used_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the voucher that was used.
     */
    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class, 'voucherID');
    }

    /**
     * Get the customer who used the voucher.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customerID');
    }

    /**
     * Get the booking associated with the voucher usage.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'bookingID', 'bookingID');
    }
}
