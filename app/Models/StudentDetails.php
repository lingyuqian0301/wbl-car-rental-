<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentDetails extends Model
{
    protected $table = 'studentdetails';
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
        'matric_card_img', // <--- Updated column name
    ];

    protected function casts(): array
    {
        return [
            'yearOfStudy' => 'integer',
        ];
    }

    public function localStudents(): HasMany
    {
        return $this->hasMany(LocalStudent::class, 'matric_number', 'matric_number');
    }

    public function internationalStudents(): HasMany
    {
        return $this->hasMany(InternationalStudent::class, 'matric_number', 'matric_number');
    }
}