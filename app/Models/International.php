<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    ];

    /**
     * Get the customer that owns this international record.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customerID', 'customerID');
    }
}
