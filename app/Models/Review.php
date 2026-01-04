<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $table = 'review';
    protected $primaryKey = 'reviewID';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'rating',
        'comment',
        'review_date',
        'bookingID',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'review_date' => 'date',
        ];
    }

    /**
     * Get the booking that owns this review.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'bookingID', 'bookingID');
    }
}
