<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Local extends Model
{
    protected $table = 'Local';
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
     * Get the customer.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customerID', 'customerID');
    }

    /**
     * Get the person details.
     */
    public function personDetails(): BelongsTo
    {
        return $this->belongsTo(PersonDetails::class, 'ic_no', 'ic_no');
    }

    /**
     * Get the local student details.
     */
    public function localStudent(): HasOne
    {
        return $this->hasOne(LocalStudent::class, 'customerID', 'customerID');
    }

    /**
     * Get the local UTM staff details.
     */
    public function localUTMStaff(): HasOne
    {
        return $this->hasOne(Local_UTMStaff::class, 'customerID', 'customerID');
    }
}

