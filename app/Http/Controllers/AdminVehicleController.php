<?php

namespace App\Http\Controllers;

use App\Models\ItemCategory;
use App\Models\Vehicle;
use App\Models\Voucher;
use App\Models\VoucherUsage;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class AdminVehicleController extends Controller
{
    public function cars(Request $request): View
    {
        $search = $request->get('search');
        
        // Get cars from car table and join with vehicle table
        $query = \App\Models\Car::with('vehicle');
        
        if ($search) {
            $query->whereHas('vehicle', function($q) use ($search) {
                $q->where('vehicle_brand', 'like', "%{$search}%")
                  ->orWhere('vehicle_model', 'like', "%{$search}%")
                  ->orWhere('plate_number', 'like', "%{$search}%");
            });
        }
        
        $cars = $query->join('vehicle', 'car.vehicleID', '=', 'vehicle.vehicleID')
            ->select('car.*', 'vehicle.vehicle_brand', 'vehicle.vehicle_model', 'vehicle.plate_number', 'vehicle.availability_status', 'vehicle.rental_price', 'vehicle.created_date')
            ->orderBy('vehicle.vehicle_brand')
            ->orderBy('vehicle.vehicle_model')
            ->paginate(10)->withQueryString();

        // Summary stats for header
        $today = Carbon::today();
        $totalCars = \App\Models\Car::count();
        $totalAvailable = \App\Models\Car::whereHas('vehicle', function($q) {
            $q->where('availability_status', 'available');
        })->count();
        $totalRented = \App\Models\Car::whereHas('vehicle', function($q) {
            $q->where('availability_status', 'rented');
        })->count();

        return view('admin.vehicles.cars', [
            'cars' => $cars,
            'search' => $search,
            'heading' => 'Cars',
            'totalCars' => $totalCars,
            'totalAvailable' => $totalAvailable,
            'totalRented' => $totalRented,
            'today' => $today,
        ]);
    }

    public function motorcycles(Request $request): View
    {
        $search = $request->get('search');
        
        // Get motorcycles from motorcycle table and join with vehicle table
        $query = \App\Models\Motorcycle::with('vehicle');
        
        if ($search) {
            $query->whereHas('vehicle', function($q) use ($search) {
                $q->where('vehicle_brand', 'like', "%{$search}%")
                  ->orWhere('vehicle_model', 'like', "%{$search}%")
                  ->orWhere('plate_number', 'like', "%{$search}%");
            });
        }
        
        $motorcycles = $query->join('vehicle', 'motorcycle.vehicleID', '=', 'vehicle.vehicleID')
            ->select('motorcycle.*', 'vehicle.vehicle_brand', 'vehicle.vehicle_model', 'vehicle.plate_number', 'vehicle.availability_status', 'vehicle.rental_price', 'vehicle.created_date')
            ->orderBy('vehicle.vehicle_brand')
            ->orderBy('vehicle.vehicle_model')
            ->paginate(10)->withQueryString();

        // Summary stats for header
        $today = Carbon::today();
        $totalMotorcycles = \App\Models\Motorcycle::count();
        $totalAvailable = \App\Models\Motorcycle::whereHas('vehicle', function($q) {
            $q->where('availability_status', 'available');
        })->count();
        $totalRented = \App\Models\Motorcycle::whereHas('vehicle', function($q) {
            $q->where('availability_status', 'rented');
        })->count();

        return view('admin.vehicles.motorcycles', [
            'motorcycles' => $motorcycles,
            'search' => $search,
            'heading' => 'Motorcycles',
            'totalMotorcycles' => $totalMotorcycles,
            'totalAvailable' => $totalAvailable,
            'totalRented' => $totalRented,
            'today' => $today,
        ]);
    }

    public function others(Request $request): View
    {
        $activeTab = $request->get('tab', 'voucher'); // voucher or reward
        
        $data = [
            'activeTab' => $activeTab,
        ];
        
        // If voucher tab is active, fetch voucher data
        if ($activeTab === 'voucher') {
            $query = Voucher::query();
            // Only add withCount if voucher_usage table exists
            if (\Schema::hasTable('voucher_usage')) {
                $query->withCount('usages');
            }

            // Search by voucher ID or voucher name
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('voucherID', 'like', "%{$search}%")
                      ->orWhere('voucher_name', 'like', "%{$search}%");
                });
            }

            // Filter by active status
            if ($request->filled('status')) {
                if ($request->status === 'active') {
                    $query->where('voucher_isActive', true);
                    // Check if expiry_date column exists before using it
                    if (\Schema::hasColumn('voucher', 'expiry_date')) {
                        $query->where(function($q) {
                            $q->whereNull('expiry_date')
                              ->orWhere('expiry_date', '>=', Carbon::today());
                        });
                    }
                    // Check if num_valid and num_applied columns exist before using them
                    if (\Schema::hasColumn('voucher', 'num_valid') && \Schema::hasColumn('voucher', 'num_applied')) {
                        $query->whereRaw('(num_valid - num_applied) > 0');
                    }
                } elseif ($request->status === 'inactive') {
                    $query->where(function($q) {
                        $q->where('voucher_isActive', false);
                        // Check if expiry_date column exists before using it
                        if (\Schema::hasColumn('voucher', 'expiry_date')) {
                            $q->orWhere(function($q2) {
                                $q2->whereNotNull('expiry_date')
                                   ->where('expiry_date', '<', Carbon::today());
                            });
                        }
                        // Check if num_valid and num_applied columns exist before using them
                        if (\Schema::hasColumn('voucher', 'num_valid') && \Schema::hasColumn('voucher', 'num_applied')) {
                            $q->orWhereRaw('(num_valid - num_applied) <= 0');
                        }
                    });
                }
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'latest');
            switch ($sortBy) {
                case 'latest':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'code_asc':
                    $query->orderBy('voucher_code', 'asc');
                    break;
                case 'code_desc':
                    $query->orderBy('voucher_code', 'desc');
                    break;
                case 'expiry_asc':
                    $query->orderBy('expiry_date', 'asc');
                    break;
                case 'expiry_desc':
                    $query->orderBy('expiry_date', 'desc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }

            $vouchers = $query->paginate(20)->withQueryString();

            // Calculate num_left for each voucher
            $vouchers->getCollection()->transform(function ($voucher) {
                // Check if num_valid and num_applied columns exist
                if (isset($voucher->num_valid) && isset($voucher->num_applied)) {
                    $voucher->num_left = $voucher->num_valid - $voucher->num_applied;
                } else {
                    $voucher->num_left = 0;
                }
                $voucher->is_active_status = $voucher->isActiveStatus;
                $voucher->active_status_text = $voucher->activeStatusText;
                return $voucher;
            });

            // Summary stats for header
            $today = Carbon::today();
            $totalVouchers = Voucher::count();
            $activeVouchersQuery = Voucher::where('voucher_isActive', true);
            // Check if expiry_date column exists before using it
            if (\Schema::hasColumn('voucher', 'expiry_date')) {
                $activeVouchersQuery->where(function($q) use ($today) {
                    $q->whereNull('expiry_date')
                      ->orWhere('expiry_date', '>=', $today);
                });
            }
            // Check if num_valid and num_applied columns exist before using them
            if (\Schema::hasColumn('voucher', 'num_valid') && \Schema::hasColumn('voucher', 'num_applied')) {
                $activeVouchersQuery->whereRaw('(num_valid - num_applied) > 0');
            }
            $activeVouchers = $activeVouchersQuery->count();
            // Check if voucher_usage table exists before querying
            $totalUsed = \Schema::hasTable('voucher_usage') ? VoucherUsage::count() : 0;
            // Check if num_applied column exists before summing
            $totalApplied = \Schema::hasColumn('voucher', 'num_applied') ? Voucher::sum('num_applied') : 0;

            $data = array_merge($data, [
                'vouchers' => $vouchers,
                'showHeader' => false, // Hide header since it's in a tab
                'totalVouchers' => $totalVouchers,
                'activeVouchers' => $activeVouchers,
                'totalUsed' => $totalUsed,
                'totalApplied' => $totalApplied,
                'today' => $today,
            ]);
        }
        
        return view('admin.vehicles.others', $data);
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
                    $q->where('vehicle_brand', 'like', "%{$search}%")
                      ->orWhere('vehicle_model', 'like', "%{$search}%")
                      ->orWhere('plate_number', 'like', "%{$search}%");
                });
            })
            ->orderBy('vehicle_brand')
            ->orderBy('vehicle_model');
    }
}

