<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Admin extends Model
{
    protected $table = 'Admin';
    protected $primaryKey = 'adminID';
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
}

