<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    protected $table = 'systemlog';
    protected $primaryKey = 'logID';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'userType',
        'action',
        'timestamp',
        'userID',
    ];

    protected function casts(): array
    {
        return [
            'timestamp' => 'datetime',
        ];
    }
}
