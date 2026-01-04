<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrantDoc extends Model
{
    protected $table = 'grantdoc';
    protected $primaryKey = 'grantID';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'grantNo',
        'grantType',
        'grant_expirydate',
        'documentID',
    ];

    protected function casts(): array
    {
        return [
            'grant_expirydate' => 'date',
        ];
    }
}
