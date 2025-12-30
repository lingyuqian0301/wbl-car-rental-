<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Booking extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    use HasFactory;

    // Support both booking (singular) and bookings (plural) tables
    protected $table = 'booking'; // Use singular table name from hastatravel.sql

    protected $primaryKey = 'bookingID'; // Use bookingID from hastatravel.sql
    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = true;

    protected $fillable = [
        'customerID',
        'vehicleID',
        'start_date',
        'end_date',
        'duration_days',
        'number_of_days',
        'total_amount',
        'booking_status',
        'keep_deposit',
        'pickup_point',
        'return_point',
        'addOns_item',
        'addOns_charge',
        'late_return_fees',
        'damage_fee',
        'cancellation_type',
        'creationDate',
        'status_update_date_time',
        'staffID',
    ];
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'total_amount' => 'decimal:2',
            'addOns_charge' => 'decimal:2',
            'late_return_fees' => 'decimal:2',
            'damage_fee' => 'decimal:2',
            'keep_deposit' => 'boolean',
            'creationDate' => 'datetime',
            'status_update_date_time' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the booking.
     * Note: booking table uses customerID, but we'll support user_id for Laravel users
     */
    public function user(): BelongsTo
    {
        // Try to find user by customerID first, then fallback to user_id if exists
        if ($this->customerID) {
            // You may need to create a Customer model that links to User
            return $this->belongsTo(User::class, 'customerID');
        }
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the vehicle for the booking.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicleID', 'vehicleID');
    }

    /**
     * Get the payments for the booking.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customerID');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'bookingID', 'bookingID');
    }

    /**
     * Get the number of days for deposit calculation.
     */
    public function getNumberOfDays(): int
    {
        return $this->number_of_days ?? $this->duration_days ?? 0;
    }

    /**
     * Get total price (alias for total_amount).
     */
    public function getTotalPriceAttribute()
    {
        return $this->total_amount;
    }

    /**
     * Get status (alias for booking_status).
     */
    public function getStatusAttribute()
    {
        return $this->booking_status;
    }

    /**
     * Set status (alias for booking_status).
     */
    public function setStatusAttribute($value)
    {
        $this->booking_status = $value;
    }
}
