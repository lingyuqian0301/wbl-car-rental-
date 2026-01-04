<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarImg extends Model
{
    protected $table = 'car_img';
    protected $primaryKey = 'imgID';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'imageType',
        'img_description',
        'documentID',
    ];
}
