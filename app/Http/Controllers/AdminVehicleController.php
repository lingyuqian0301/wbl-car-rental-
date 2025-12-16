<?php

namespace App\Http\Controllers;

use App\Models\ItemCategory;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminVehicleController extends Controller
{
    public function cars(Request $request): View
    {
        $category = ItemCategory::where('slug', 'car')->first();
        $categories = ItemCategory::where('is_active', true)->get();
        $selectedCategory = $request->get('category', 'all');
        
        $vehicles = $this->filteredVehicles($request, $category?->id)->paginate(10)->withQueryString();

        return view('admin.vehicles.cars', [
            'vehicles' => $vehicles,
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
            'heading' => 'Cars',
        ]);
    }

    public function motorcycles(Request $request): View
    {
        $category = ItemCategory::where('slug', 'motorcycle')->orWhere('slug', 'motorcycles')->first();
        $categories = ItemCategory::where('is_active', true)->get();
        $selectedCategory = $request->get('category', 'all');
        
        $vehicles = $this->filteredVehicles($request, $category?->id)->paginate(10)->withQueryString();

        return view('admin.vehicles.motorcycles', [
            'vehicles' => $vehicles,
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
            'heading' => 'Motorcycles',
        ]);
    }

    public function others(Request $request): View
    {
        $categories = ItemCategory::where('is_active', true)->get();
        $selectedCategory = $request->get('category', 'all');
        
        // "Other" shows all categories, or filter by selected category
        $categoryId = null;
        if ($selectedCategory !== 'all') {
            $cat = ItemCategory::find($selectedCategory);
            $categoryId = $cat?->id;
        }
        
        $vehicles = $this->filteredVehicles($request, $categoryId)->paginate(10)->withQueryString();

        return view('admin.vehicles.others', [
            'vehicles' => $vehicles,
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
            'heading' => 'Other Vehicles',
        ]);
    }

    public function show(Vehicle $vehicle): View
    {
        $vehicle->load(['bookings.user', 'category']);

        return view('admin.vehicles.show', [
            'vehicle' => $vehicle,
        ]);
    }

    protected function filteredVehicles(Request $request, $categoryId = null)
    {
        $search = $request->get('search');

        return Vehicle::query()
            ->when($categoryId, function ($query, $categoryId) {
                $query->where('item_category_id', $categoryId);
            })
            ->when($search, function ($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('brand', 'like', "%{$search}%")
                      ->orWhere('model', 'like', "%{$search}%")
                      ->orWhere('registration_number', 'like', "%{$search}%");
                });
            })
            ->with('category')
            ->orderBy('brand')
            ->orderBy('model');
    }
}

