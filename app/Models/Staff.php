<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Staff extends Model
{
    protected $table = 'staff';
    protected $primaryKey = 'staffID';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'ic_no',
        'userID',
    ];

    /**
     * Get the user that owns this staff.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'userID', 'id');
    }

    /**
     * Get the staff detail for this staff.
     */
    public function staffDetail(): HasOne
    {
        return $this->hasOne(StaffDetail::class, 'staffID');
    }

    /**
     * Get the staff IT record for this staff.
     */
    public function staffIt(): HasOne
    {
        return $this->hasOne(StaffIt::class, 'staffID');
    }

    /**
     * Get the runner record for this staff.
     */
    public function runner(): HasOne
    {
        return $this->hasOne(Runner::class, 'staffID');
    }
}
