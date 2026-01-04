<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentDetail extends Model
{
    protected $table = 'studentdetails';
    protected $primaryKey = 'customerID';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'customerID',
        'matric_number',
        'college',
        'faculty',
        'programme',
        'yearOfStudy',
    ];

    /**
     * Get the customer that owns this student detail record.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customerID', 'customerID');
    }
}
