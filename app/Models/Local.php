<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Local extends Model
{
    protected $table = 'local';
    protected $primaryKey = 'customerID';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'customerID',
        'ic_no',
        'stateOfOrigin',
    ];

    /**
     * Get the customer that owns this local record.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customerID', 'customerID');
    }

    /**
     * Get the person details for this local customer.
     */
    public function personDetails(): BelongsTo
    {
        return $this->belongsTo(PersonDetails::class, 'ic_no', 'ic_no');
    }

    /**
     * Get the local student record.
     */
    public function localStudent(): HasOne
    {
        return $this->hasOne(LocalStudent::class, 'customerID', 'customerID');
    }

    /**
     * Get the local UTM staff record.
     */
    public function localUtmStaff(): HasOne
    {
        return $this->hasOne(Local_UTMStaff::class, 'customerID', 'customerID');
    }
}
