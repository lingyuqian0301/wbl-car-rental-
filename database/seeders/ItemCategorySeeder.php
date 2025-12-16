<?php

namespace Database\Seeders;

use App\Models\ItemCategory;
use Illuminate\Database\Seeder;

class ItemCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Cars',
                'slug' => 'car',
                'description' => 'Various types of cars for rent',
                'is_active' => true,
            ],
            [
                'name' => 'SUVs',
                'slug' => 'suv',
                'description' => 'Sports Utility Vehicles for family and group travels',
                'is_active' => true,
            ],
            [
                'name' => 'Vans',
                'slug' => 'van',
                'description' => 'Spacious vans for larger groups or cargo',
                'is_active' => true,
            ],
            [
                'name' => 'Trucks',
                'slug' => 'truck',
                'description' => 'Light and heavy-duty trucks for various needs',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            ItemCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
