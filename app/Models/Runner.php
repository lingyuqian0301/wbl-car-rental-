<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Runner extends Model
{
    protected $table = 'runner';
    protected $primaryKey = 'staffID';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'staffID',
        'commission',
    ];

    protected function casts(): array
    {
        return [
            'commission' => 'decimal:2',
        ];
    }

    /**
     * Get the staff that owns this runner record.
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staffID', 'staffID');
    }
}
