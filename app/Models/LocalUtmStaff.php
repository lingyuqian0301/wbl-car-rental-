<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocalUtmStaff extends Model
{
    protected $table = 'local_utmstaff';
    protected $primaryKey = 'customerID';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'customerID',
        'staff_number',
    ];

    /**
     * Get the customer that owns this local UTM staff record.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customerID', 'customerID');
    }
}
