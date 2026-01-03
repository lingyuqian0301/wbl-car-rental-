<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;


class Booking extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    use HasFactory;

    protected $table = 'Booking';
    protected $primaryKey = 'bookingID';
    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'lastUpdateDate',
        'rental_start_date',
        'rental_end_date',
        'duration',
        'deposit_amount',
        'rental_amount',
        'pickup_point',
        'return_point',
        'addOns_item',
        'booking_status',
        'customerID',
        'vehicleID',
    ];

    protected function casts(): array
    {
        return [
            'lastUpdateDate' => 'datetime',
            'rental_start_date' => 'date',
            'rental_end_date' => 'date',
            'duration' => 'integer',
            'deposit_amount' => 'decimal:2',
            'rental_amount' => 'decimal:2',
        ];
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

    /**
     * Get start_date (alias for rental_start_date).
     */
    public function getStartDateAttribute()
    {
        return $this->rental_start_date;
    }

    /**
     * Get end_date (alias for rental_end_date).
     */
    public function getEndDateAttribute()
    {
        return $this->rental_end_date;
    }

    /**
     * Get total_amount (alias for rental_amount).
     */
    public function getTotalAmountAttribute()
    {
        return $this->rental_amount;
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

    /**
     * Get the invoice for the booking.
     */
    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class, 'bookingID', 'bookingID');
    }

    /**
     * Get the payments for the booking.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'bookingID', 'bookingID');
    }

    /**
     * Get the additional charges for the booking.
     */
    public function additionalCharges(): HasOne
    {
        return $this->hasOne(AdditionalCharges::class, 'bookingID', 'bookingID');
    }

    /**
     * Get the review for the booking.
     */
    public function review(): HasOne
    {
        return $this->hasOne(Review::class, 'bookingID', 'bookingID');
    }

    /**
     * Get the number of days for deposit calculation.
     */
    public function getNumberOfDays(): int
    {
        return $this->duration ?? 0;
    }
}
