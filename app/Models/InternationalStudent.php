<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternationalStudent extends Model
{
    protected $table = 'InternationalStudent';
    protected $primaryKey = 'customerID';
    public $incrementing = false;
    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'customerID',
        'matric_number',
    ];

    /**
     * Get the international customer.
     */
    public function international(): BelongsTo
    {
        return $this->belongsTo(International::class, 'customerID', 'customerID');
    }

    /**
     * Get the student details.
     */
    public function studentDetails(): BelongsTo
    {
        return $this->belongsTo(StudentDetails::class, 'matric_number', 'matric_number');
    }
}

