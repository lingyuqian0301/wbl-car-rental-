<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class International_UTMStaff extends Model
{
    protected $table = 'International_UTMStaff';
    protected $primaryKey = 'customerID';
    public $incrementing = false;
    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'customerID',
        'staff_number',
    ];

    /**
     * Get the international customer.
     */
    public function international(): BelongsTo
    {
        return $this->belongsTo(International::class, 'customerID', 'customerID');
    }

    /**
     * Get the staff details.
     */
    public function staffDetails(): BelongsTo
    {
        return $this->belongsTo(StaffDetails::class, 'staff_number', 'staff_number');
    }
}

