<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItemCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get vehicles in this category.
     */
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'item_category_id');
    }
}
