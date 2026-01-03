<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentDetails extends Model
{
    protected $table = 'StudentDetails';
    protected $primaryKey = 'matric_number';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'matric_number',
        'college',
        'faculty',
        'programme',
        'yearOfStudy',
    ];

    protected function casts(): array
    {
        return [
            'yearOfStudy' => 'integer',
        ];
    }

    /**
     * Get all local students with this matric number.
     */
    public function localStudents(): HasMany
    {
        return $this->hasMany(LocalStudent::class, 'matric_number', 'matric_number');
    }

    /**
     * Get all international students with this matric number.
     */
    public function internationalStudents(): HasMany
    {
        return $this->hasMany(InternationalStudent::class, 'matric_number', 'matric_number');
    }
}

