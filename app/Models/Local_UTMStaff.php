<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Local_UTMStaff extends Model
{
    protected $table = 'Local_UTMStaff';
    protected $primaryKey = 'customerID';
    public $incrementing = false;
    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'customerID',
        'staff_number',
    ];

    /**
     * Get the local customer.
     */
    public function local(): BelongsTo
    {
        return $this->belongsTo(Local::class, 'customerID', 'customerID');
    }

    /**
     * Get the staff details.
     */
    public function staffDetails(): BelongsTo
    {
        return $this->belongsTo(StaffDetails::class, 'staff_number', 'staff_number');
    }
}

