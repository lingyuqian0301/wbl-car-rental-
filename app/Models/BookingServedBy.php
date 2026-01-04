<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingServedBy extends Model
{
    protected $table = 'booking_served_by';
    
    protected $fillable = [
        'booking_id',
        'served_by_user_id',
        'served_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'served_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'bookingID');
    }

    public function servedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'served_by_user_id');
    }
}










