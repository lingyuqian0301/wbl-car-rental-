<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffDetail extends Model
{
    protected $table = 'staffdetail';
    protected $primaryKey = 'staffID';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'staffID',
        'staff_number',
        'position',
        'college',
    ];

    /**
     * Get the staff that owns this staff detail record.
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staffID', 'staffID');
    }
}
