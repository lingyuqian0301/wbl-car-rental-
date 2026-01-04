<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Roadtax extends Model
{
    protected $table = 'roadtax';
    protected $primaryKey = 'roadtax_ID';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'roadtax_certificationNo',
        'roadtax_expirydate',
        'documentID',
    ];

    protected function casts(): array
    {
        return [
            'roadtax_expirydate' => 'date',
        ];
    }
}
