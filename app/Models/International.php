<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class International extends Model
{
    protected $table = 'international';
    protected $primaryKey = 'customerID';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'customerID',
        'passport_no',
        'countryOfOrigin',
        'passport_img',
        'license_img',
    ];

    /**
     * Get the customer that owns this international record.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customerID', 'customerID');
    }

    /**
     * Get the international student record.
     */
    public function internationalStudent(): HasOne
    {
        return $this->hasOne(InternationalStudent::class, 'customerID', 'customerID');
    }

    /**
     * Get the international UTM staff record.
     */
    public function internationalUtmStaff(): HasOne
    {
        return $this->hasOne(International_UTMStaff::class, 'customerID', 'customerID');
    }
}
