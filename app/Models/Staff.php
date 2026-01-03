<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Staff extends Model
{
    protected $table = 'Staff';
    protected $primaryKey = 'staffID';
    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'ic_no',
        'userID',
    ];

    /**
     * Get the person details.
     */
    public function personDetails(): BelongsTo
    {
        return $this->belongsTo(PersonDetails::class, 'ic_no', 'ic_no');
    }

    /**
     * Get the user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'userID', 'userID');
    }

    /**
     * Get the runner details.
     */
    public function runner(): HasOne
    {
        return $this->hasOne(Runner::class, 'staffID', 'staffID');
    }

    /**
     * Get the IT staff details.
     */
    public function staffIT(): HasOne
    {
        return $this->hasOne(StaffIT::class, 'staffID', 'staffID');
    }
}

