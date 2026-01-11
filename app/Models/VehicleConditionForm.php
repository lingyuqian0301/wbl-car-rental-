<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleConditionForm extends Model
{
    use HasFactory;

    // Specify the correct table name
    protected $table = 'vehicleconditionform';
    
    // Based on usage in PickupController: $form->formID
    protected $primaryKey = 'formID';
    
    protected $fillable = [
        'form_type',
        'rental_agreement',
        'odometer_reading',
        'fuel_level',
        'fuel_img',
        'scratches_notes',
        'reported_dated_time',
        'bookingID',
    ];

    // Relationship to Booking
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'bookingID', 'bookingID');
    }

    // Relationship to Images
    public function images()
    {
        return $this->hasMany(VehicleConditionImage::class, 'formID', 'formID');
    }
}