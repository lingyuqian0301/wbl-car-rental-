<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PersonDetails extends Model
{
    protected $table = 'PersonDetails';
    protected $primaryKey = 'ic_no';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'ic_no',
        'fullname',
        'ic_img',
    ];

    /**
     * Get all local customers with this IC number.
     */
    public function localCustomers(): HasMany
    {
        return $this->hasMany(Local::class, 'ic_no', 'ic_no');
    }

    /**
     * Get all staff with this IC number.
     */
    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class, 'ic_no', 'ic_no');
    }

    /**
     * Get all admins with this IC number.
     */
    public function admins(): HasMany
    {
        return $this->hasMany(Admin::class, 'ic_no', 'ic_no');
    }
}

