<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffIt extends Model
{
    protected $table = 'staffit';
    protected $primaryKey = 'staffID';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'staffID',
        'salary',
    ];

    protected function casts(): array
    {
        return [
            'salary' => 'decimal:2',
        ];
    }

    /**
     * Get the staff that owns this staff IT record.
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staffID', 'staffID');
    }
}
