<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{

    protected $table = 'cars';
    protected $primaryKey = 'vehicleID';
    public $incrementing = true;
    protected $keyType = 'int';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'brand',
        'model',
        'registration_number',
        'daily_rate',
        'status',
        'description',
        'item_category_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'daily_rate' => 'decimal:2',
        ];
    }

    /**
     * Get the full model name (brand + model).
     */
    public function getFullModelAttribute(): string
    {
        return "{$this->brand} {$this->model}";
    }

    /**
     * Get the bookings for the vehicle.
     */
    public function bookings()
{
    return $this->hasMany(Booking::class, 'vehicleID');
}

    /**
     * Get the category for the vehicle.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ItemCategory::class, 'item_category_id');
    }
}
