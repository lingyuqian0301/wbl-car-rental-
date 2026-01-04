<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceReminder extends Model
{
    protected $fillable = [
        'vehicle_id',
        'service_type',
        'reminder_date',
        'status', // pending, completed, overdue
        'notes',
        'mileage_interval',
        'days_interval'
    ];

    protected $casts = [
        'reminder_date' => 'date',
        'mileage_interval' => 'integer',
        'days_interval' => 'integer'
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
