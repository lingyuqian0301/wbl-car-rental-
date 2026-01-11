<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RunnerNotification extends Model
{
    protected $table = 'runner_notifications';

    protected $fillable = [
        'runner_user_id',
        'booking_id',
        'type',
        'message',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    /**
     * Get the runner user
     */
    public function runner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'runner_user_id', 'userID');
    }

    /**
     * Get the booking
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'bookingID');
    }
}

