<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleConditionImage extends Model
{
    use HasFactory;

    // Specify the correct table name
    protected $table = 'vehicleconditionimage';
    
    // Assuming a primary key like imageID based on your project's naming convention
    protected $primaryKey = 'imageID';

    protected $fillable = [
        'image_path',
        'image_taken_time',
        'formID',
    ];

    public function form()
    {
        return $this->belongsTo(VehicleConditionForm::class, 'formID', 'formID');
    }
}