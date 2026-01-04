<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Admin extends Model
{
    protected $table = 'admin';
    protected $primaryKey = 'adminID';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'ic_no',
        'userID',
    ];

    /**
     * Get the user that owns this admin.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'userID', 'id');
    }
}
