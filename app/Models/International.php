<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class International extends Model
{
    protected $table = 'International';
    protected $primaryKey = 'customerID';
    public $incrementing = false;
    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'customerID',
        'passport_no',
        'countryOfOrigin',
    ];

    /**
     * Get the customer.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customerID', 'customerID');
    }

    /**
     * Get the international student details.
     */
    public function internationalStudent(): HasOne
    {
        return $this->hasOne(InternationalStudent::class, 'customerID', 'customerID');
    }

    /**
     * Get the international UTM staff details.
     */
    public function internationalUTMStaff(): HasOne
    {
        return $this->hasOne(International_UTMStaff::class, 'customerID', 'customerID');
    }
}

