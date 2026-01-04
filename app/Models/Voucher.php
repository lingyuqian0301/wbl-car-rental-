<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Voucher extends Model
{
    protected $table = 'voucher';
    protected $primaryKey = 'voucherID';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'voucher_code',
        'voucher_name',
        'description',
        'discount_type',
        'discount_value',
        'expiry_date',
        'num_valid',
        'num_applied',
        'restrictions',
        'voucher_isActive',
        'isActive', // Keep for backward compatibility
        'loyaltyCardID',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'expiry_date' => 'date',
            'num_valid' => 'integer',
            'num_applied' => 'integer',
            'isActive' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the voucher usage records.
     */
    public function usages(): HasMany
    {
        return $this->hasMany(VoucherUsage::class, 'voucherID');
    }


    /**
     * Get number of left vouchers.
     */
    public function getNumLeftAttribute(): int
    {
        if (isset($this->attributes['num_valid']) && isset($this->attributes['num_applied'])) {
            return max(0, ($this->attributes['num_valid'] ?? 0) - ($this->attributes['num_applied'] ?? 0));
        }
        return 0;
    }

    /**
     * Get the isActive attribute (maps to voucher_isActive).
     */
    public function getIsActiveAttribute()
    {
        return $this->attributes['voucher_isActive'] ?? $this->attributes['isActive'] ?? false;
    }

    /**
     * Set the isActive attribute (maps to voucher_isActive).
     */
    public function setIsActiveAttribute($value)
    {
        $this->attributes['voucher_isActive'] = $value ? 1 : 0;
    }

    /**
     * Check if voucher is active (not expired and not all used).
     */
    public function getIsActiveStatusAttribute(): bool
    {
        if (!$this->isActive) {
            return false;
        }

        if ($this->expiry_date && Carbon::parse($this->expiry_date)->isPast()) {
            return false;
        }

        if ($this->num_left <= 0) {
            return false;
        }

        return true;
    }

    /**
     * Get active status text.
     */
    public function getActiveStatusTextAttribute(): string
    {
        if (!$this->isActive) {
            return 'Inactive';
        }

        if ($this->expiry_date && Carbon::parse($this->expiry_date)->isPast()) {
            return 'Expired';
        }

        if ($this->num_left <= 0) {
            return 'All Used';
        }

        return 'Active';
    }

    /**
     * Get discount display (with percentage or amount).
     */
    public function getDiscountDisplayAttribute(): string
    {
        if ($this->discount_type === 'percentage' || $this->discount_type === 'Percentage') {
            return $this->discount_value . '%';
        }
        return 'RM ' . number_format($this->discount_value, 2);
    }
}
