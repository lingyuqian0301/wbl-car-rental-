<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Staff extends Model
{
    protected $table = 'staff';
    protected $primaryKey = 'staffID';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'ic_no',
        'ic_img',
        'userID',
    ];

    /**
     * Get the user that owns this staff.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'userID', 'userID');
    }

    /**
     * Get the person details for this staff.
     */
    public function personDetails(): BelongsTo
    {
        return $this->belongsTo(PersonDetails::class, 'ic_no', 'ic_no');
    }

    /**
     * Get the staff IT record for this staff.
     */
    public function staffIt(): HasOne
    {
        return $this->hasOne(StaffIT::class, 'staffID', 'staffID');
    }

    /**
     * Get the runner record for this staff.
     */
    public function runner(): HasOne
    {
        return $this->hasOne(Runner::class, 'staffID', 'staffID');
    }

    /**
     * Get vehicle maintenance records handled by this staff.
     */
    public function vehicleMaintenance()
    {
        return $this->hasMany(VehicleMaintenance::class, 'staffID', 'staffID');
    }

    /**
     * Get the route key for the model (for route model binding).
     */
    public function getRouteKeyName(): string
    {
        return 'staffID';
    }
}
