<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    use HasFactory;
 
    protected $table = 'booking';
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
        'staff_served',
        'deposit_refund_status',
        'deposit_handled_by',
        'deposit_fine_amount',
        'deposit_refund_amount',
        'deposit_customer_choice',
    ];

    protected function casts(): array
    {
        return [
            'lastUpdateDate' => 'datetime',
            'rental_start_date' => 'datetime',
            'rental_end_date' => 'datetime',
            'duration' => 'integer',
            'deposit_amount' => 'decimal:2',
            'rental_amount' => 'decimal:2',
            'deposit_fine_amount' => 'decimal:2',
            'deposit_refund_amount' => 'decimal:2',
        ];
    }

    /**
     * Get the customer that owns this booking.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customerID', 'customerID');
    }

    /**
     * Get the user through customer relationship (helper accessor).
     * This allows $booking->user to work even though there's no direct relationship.
     */
    public function getUserAttribute()
    {
        return $this->customer ? $this->customer->user : null;
    }

    /**
     * Get the user who confirmed this booking.
     */
    public function confirmedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by', 'userID');
    }

    /**
     * Get the user who completed this booking.
     */
    public function completedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by', 'userID');
    }

    /**
     * Get the vehicle for this booking.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicleID', 'vehicleID');
    }

    /**
     * Get the payments for this booking.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'bookingID', 'bookingID');
    }

    /**
     * Get the additional charges for this booking.
     */
    public function additionalCharges(): HasOne
    {
        return $this->hasOne(AdditionalCharges::class, 'bookingID', 'bookingID');
    }

    /**
     * Get the invoice for this booking.
     */
    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class, 'bookingID', 'bookingID');
    }

    /**
     * Get the review for this booking.
     */
    public function review(): HasOne
    {
        return $this->hasOne(Review::class, 'bookingID', 'bookingID');
    }

    /**
     * Get total price (deposit + rental amount).
     */
    public function getTotalPriceAttribute()
    {
        return ($this->deposit_amount ?? 0) + ($this->rental_amount ?? 0);
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
     * Get the number of days for deposit calculation.
     */
    public function getNumberOfDays(): int
    {
        return $this->duration ?? 0;
    }

    /**
     * Get the route key for the model (for route model binding).
     */
    public function getRouteKeyName(): string
    {
        return 'bookingID';
    }

    /**
     * Determine the current booking step for resume functionality.
     * Returns: ['step' => 'agreement|pickup|return|completed', 'route' => route_name, 'label' => button_text]
     */
    public function getResumeStep()
    {
        // Check if agreement is accepted (if there's a verified payment, they've likely accepted agreement)
        $hasAgreedAndDownloaded = $this->payments->where('payment_status', 'Verified')->first() !== null;

        // If no verified payment, they haven't completed agreement step yet
        if (!$hasAgreedAndDownloaded) {
            return [
                'step' => 'agreement',
                'route' => route('agreement.show', $this->bookingID),
                'label' => 'Continue Booking',
            ];
        }

        // Agreement is done, check if pickup is done
        // Pickup would be indicated by pickup_point being set and booking progressing
        // For now, we check if they've moved past agreement (payment verified)
        // Since we don't have explicit pickup_completed flag, we assume they proceed to pickup after agreement
        // In a real scenario, you'd check for a pickup_completed_at or similar
        
        // If booking status is 'Completed', they've finished everything
        if ($this->booking_status === 'Completed') {
            return [
                'step' => 'completed',
                'route' => null,
                'label' => 'Completed',
            ];
        }

        // Default: if agreement done but pickup status unclear, offer pickup
        // This assumes linear progression: Agreement → Pickup → Return
        return [
            'step' => 'pickup',
            'route' => route('pickup.show', $this->bookingID),
            'label' => 'Proceed to Pickup',
        ];
    }

    /**
     * Check if this booking is read by a specific user.
     */
    public function isReadBy($userId): bool
    {
        try {
            return BookingReadStatus::where('booking_id', $this->bookingID)
                ->where('user_id', $userId)
                ->where('is_read', true)
                ->exists();
        } catch (\Exception $e) {
            // If table doesn't exist, return false (unread)
            return false;
        }
    }
}
