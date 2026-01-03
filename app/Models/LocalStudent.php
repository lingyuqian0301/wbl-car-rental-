<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocalStudent extends Model
{
    protected $table = 'LocalStudent';
    protected $primaryKey = 'customerID';
    public $incrementing = false;
    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'customerID',
        'matric_number',
    ];

    /**
     * Get the local customer.
     */
    public function local(): BelongsTo
    {
        return $this->belongsTo(Local::class, 'customerID', 'customerID');
    }

    /**
     * Get the student details.
     */
    public function studentDetails(): BelongsTo
    {
        return $this->belongsTo(StudentDetails::class, 'matric_number', 'matric_number');
    }
}

