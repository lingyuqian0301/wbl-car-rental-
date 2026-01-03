<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StaffDetails extends Model
{
    protected $table = 'StaffDetails';
    protected $primaryKey = 'staff_number';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'staff_number',
        'position',
        'college',
    ];

    /**
     * Get all local UTM staff with this staff number.
     */
    public function localUTMStaff(): HasMany
    {
        return $this->hasMany(Local_UTMStaff::class, 'staff_number', 'staff_number');
    }

    /**
     * Get all international UTM staff with this staff number.
     */
    public function internationalUTMStaff(): HasMany
    {
        return $this->hasMany(International_UTMStaff::class, 'staff_number', 'staff_number');
    }
}

