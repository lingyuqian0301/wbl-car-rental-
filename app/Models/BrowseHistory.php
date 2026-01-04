<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BrowseHistory extends Model
{
    protected $table = 'browsehistory';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'vehicleID',
        'customerID',
    ];

    /**
     * Get the vehicle for this browse history.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicleID', 'vehicleID');
    }

    /**
     * Get the customer for this browse history.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customerID', 'customerID');
    }
}
