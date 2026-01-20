<?php

namespace App\Http\Controllers;

use App\Models\ItemCategory;
use App\Models\Vehicle;
use App\Models\Voucher;
use App\Models\VoucherUsage;
use App\Models\AdminNotification;
use App\Traits\HandlesGoogleDriveUploads;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Schema\Blueprint;
use Carbon\Carbon;

class AdminVehicleController extends Controller
{
    use HandlesGoogleDriveUploads;
    public function cars(Request $request): View
    {
        $search = $request->get('search');
        $sortBy = $request->get('sort_by', 'vehicle_id_asc');
        $filterStatus = $request->get('filter_status');
        $filterIsActive = $request->get('filter_isactive');
        
        // Get cars from car table and join with vehicle table
        $query = \App\Models\Car::with('vehicle')
            ->join('vehicle', 'car.vehicleID', '=', 'vehicle.vehicleID')
            ->select('car.*', 'vehicle.vehicle_brand', 'vehicle.vehicle_model', 'vehicle.plate_number', 'vehicle.availability_status', 'vehicle.rental_price', 'vehicle.created_date', 'vehicle.vehicleID', 'vehicle.isActive');
        
        // Search (keep current search function)
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('vehicle.vehicle_brand', 'like', "%{$search}%")
                  ->orWhere('vehicle.vehicle_model', 'like', "%{$search}%")
                  ->orWhere('vehicle.plate_number', 'like', "%{$search}%");
            });
        }
        
        // Filters - only status and isActive
        if ($filterStatus) {
            $query->where('vehicle.availability_status', $filterStatus);
        }
        if ($filterIsActive !== null && $filterIsActive !== '') {
            $query->where('vehicle.isActive', $filterIsActive == '1');
        }
        
        // Sorting
        switch ($sortBy) {
            case 'vehicle_id_asc':
                $query->orderBy('vehicle.vehicleID', 'ASC');
                break;
            case 'highest_rented':
                // Sort by number of bookings (highest first)
                $query->leftJoin('booking', 'vehicle.vehicleID', '=', 'booking.vehicleID')
                      ->selectRaw('car.*, vehicle.vehicle_brand, vehicle.vehicle_model, vehicle.plate_number, vehicle.availability_status, vehicle.rental_price, vehicle.created_date, vehicle.vehicleID, vehicle.isActive, COUNT(booking.bookingID) as booking_count')
                      ->groupBy('vehicle.vehicleID', 'car.carID')
                      ->orderBy('booking_count', 'DESC');
                break;
            case 'highest_rental_price':
                $query->orderBy('vehicle.rental_price', 'DESC');
                break;
            case 'plate_no_asc':
                $query->orderBy('vehicle.plate_number', 'ASC');
                break;
            default:
                $query->orderBy('vehicle.vehicleID', 'ASC');
        }
        
        $cars = $query->paginate(10)->withQueryString();

        // Summary stats for header
        $today = Carbon::today();
        $totalCars = \App\Models\Car::count();
        $totalAvailable = \App\Models\Car::whereHas('vehicle', function($q) {
            $q->where('availability_status', 'available');
        })->count();
        $totalRented = \App\Models\Car::whereHas('vehicle', function($q) {
            $q->where('availability_status', 'rented');
        })->count();
        
        // Get unique values for filters
        $brands = \App\Models\Vehicle::whereHas('car')->distinct()->pluck('vehicle_brand')->filter()->sort()->values();
        $models = \App\Models\Vehicle::whereHas('car')->distinct()->pluck('vehicle_model')->filter()->sort()->values();
        $seatings = \App\Models\Car::distinct()->pluck('seating_capacity')->filter()->sort()->values();
        $transmissions = \App\Models\Car::distinct()->pluck('transmission')->filter()->sort()->values();
        $carTypes = \App\Models\Car::distinct()->pluck('car_type')->filter()->sort()->values();
        $statuses = ['available', 'rented', 'maintenance', 'unavailable'];

        return view('admin.vehicles.cars', [
            'cars' => $cars,
            'search' => $search,
            'sortBy' => $sortBy,
            'filterStatus' => $filterStatus,
            'filterIsActive' => $filterIsActive,
            'statuses' => $statuses,
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
        $sortBy = $request->get('sort_by', 'vehicle_id_asc');
        $filterStatus = $request->get('filter_status');
        $filterIsActive = $request->get('filter_isactive');
        
        // Get motorcycles from motorcycle table and join with vehicle table
        $query = \App\Models\Motorcycle::with('vehicle')
            ->join('vehicle', 'motorcycle.vehicleID', '=', 'vehicle.vehicleID')
            ->select('motorcycle.*', 'vehicle.vehicle_brand', 'vehicle.vehicle_model', 'vehicle.plate_number', 'vehicle.availability_status', 'vehicle.rental_price', 'vehicle.created_date', 'vehicle.vehicleID', 'vehicle.isActive');
        
        // Search (keep current search function)
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('vehicle.vehicle_brand', 'like', "%{$search}%")
                  ->orWhere('vehicle.vehicle_model', 'like', "%{$search}%")
                  ->orWhere('vehicle.plate_number', 'like', "%{$search}%");
            });
        }
        
        // Filters - only status and isActive
        if ($filterStatus) {
            $query->where('vehicle.availability_status', $filterStatus);
        }
        if ($filterIsActive !== null && $filterIsActive !== '') {
            $query->where('vehicle.isActive', $filterIsActive == '1');
        }
        
        // Sorting
        switch ($sortBy) {
            case 'vehicle_id_asc':
                $query->orderBy('vehicle.vehicleID', 'ASC');
                break;
            case 'highest_rented':
                // Sort by number of bookings (highest first)
                $query->leftJoin('booking', 'vehicle.vehicleID', '=', 'booking.vehicleID')
                      ->selectRaw('motorcycle.*, vehicle.vehicle_brand, vehicle.vehicle_model, vehicle.plate_number, vehicle.availability_status, vehicle.rental_price, vehicle.created_date, vehicle.vehicleID, vehicle.isActive, COUNT(booking.bookingID) as booking_count')
                      ->groupBy('vehicle.vehicleID', 'motorcycle.motorcycleID')
                      ->orderBy('booking_count', 'DESC');
                break;
            case 'highest_rental_price':
                $query->orderBy('vehicle.rental_price', 'DESC');
                break;
            case 'plate_no_asc':
                $query->orderBy('vehicle.plate_number', 'ASC');
                break;
            default:
                $query->orderBy('vehicle.vehicleID', 'ASC');
        }
        
        $motorcycles = $query->paginate(10)->withQueryString();

        // Summary stats for header
        $today = Carbon::today();
        $totalMotorcycles = \App\Models\Motorcycle::count();
        $totalAvailable = \App\Models\Motorcycle::whereHas('vehicle', function($q) {
            $q->where('availability_status', 'available');
        })->count();
        $totalRented = \App\Models\Motorcycle::whereHas('vehicle', function($q) {
            $q->where('availability_status', 'rented');
        })->count();
        
        // Get unique values for filters
        $brands = \App\Models\Vehicle::whereHas('motorcycle')->distinct()->pluck('vehicle_brand')->filter()->sort()->values();
        $models = \App\Models\Vehicle::whereHas('motorcycle')->distinct()->pluck('vehicle_model')->filter()->sort()->values();
        $motorTypes = \App\Models\Motorcycle::distinct()->pluck('motor_type')->filter()->sort()->values();
        $statuses = ['available', 'rented', 'maintenance', 'unavailable'];

        return view('admin.vehicles.motorcycles', [
            'motorcycles' => $motorcycles,
            'search' => $search,
            'sortBy' => $sortBy,
            'filterStatus' => $filterStatus,
            'filterIsActive' => $filterIsActive,
            'statuses' => $statuses,
            'heading' => 'Motorcycles',
            'totalMotorcycles' => $totalMotorcycles,
            'totalAvailable' => $totalAvailable,
            'totalRented' => $totalRented,
            'today' => $today,
        ]);
    }

    public function others(Request $request): View
    {
        $activeTab = $request->get('tab', 'vehicle'); // vehicle, voucher, or reward
        
        $data = [
            'activeTab' => $activeTab,
        ];
        
        // If vehicle tab is active, fetch vehicle data
        if ($activeTab === 'vehicle') {
            $search = $request->get('search');
            $filterType = $request->get('filter_type'); // all, car, motor, other
            $filterIsActive = $request->get('filter_isactive');
            
            $query = Vehicle::query();
            
            // Search by plate number
            if ($search) {
                $query->where('plate_number', 'like', "%{$search}%");
            }
            
            // Filters
            if ($filterType) {
                if ($filterType === 'car') {
                    $query->whereHas('car');
                } elseif ($filterType === 'motor') {
                    $query->whereHas('motorcycle');
                } elseif ($filterType === 'other') {
                    $query->whereDoesntHave('car')->whereDoesntHave('motorcycle');
                }
            }
            if ($filterIsActive !== null && $filterIsActive !== '') {
                $query->where('isActive', $filterIsActive == 1);
            }
            
            // Default sort: ASC vehicle ID (no sort function, but usually display based on this)
            $query->orderBy('vehicleID', 'ASC');
            
            $vehicles = $query->with(['car', 'motorcycle'])->paginate(20)->withQueryString();
            
            // Summary stats
            $today = Carbon::today();
            $totalVehicles = Vehicle::count();
            $totalCars = Vehicle::whereHas('car')->count();
            $totalMotors = Vehicle::whereHas('motorcycle')->count();
            
            // Get unique values for filters
            $brands = Vehicle::distinct()->pluck('vehicle_brand')->filter()->sort()->values();
            $models = Vehicle::distinct()->pluck('vehicle_model')->filter()->sort()->values();
            
            $data = array_merge($data, [
                'vehicles' => $vehicles,
                'search' => $search,
                'filterType' => $filterType,
                'filterIsActive' => $filterIsActive,
                'totalVehicles' => $totalVehicles,
                'totalCars' => $totalCars,
                'totalMotors' => $totalMotors,
                'today' => $today,
            ]);
        }
        
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
                    $query->orderBy('voucherID', 'desc');
                    break;
                case 'oldest':
                    $query->orderBy('voucherID', 'asc');
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
                    $query->orderBy('voucherID', 'desc');
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
        $vehicle->load([
            'bookings.customer.user',
            'car',
            'motorcycle',
            'owner.personDetails',
            'maintenances' => function($query) {
                $query->orderBy('service_date', 'desc');
            },
            'documents'
            // Don't eager load carImages since vehicleID column might not exist yet
        ]);

        // Get car images from car_img table
        // Check which table exists and if vehicleID column exists
        $carImages = collect([]);
        $tableName = null;
        
        // Determine which table exists: Car_Img or car_img
        if (Schema::hasTable('Car_Img')) {
            $tableName = 'Car_Img';
        } elseif (Schema::hasTable('car_img')) {
            $tableName = 'car_img';
        }
        
        if ($tableName) {
            // Check if vehicleID column exists
            $hasVehicleID = Schema::hasColumn($tableName, 'vehicleID');
            
            if ($hasVehicleID) {
                // If vehicleID column exists, use it directly
                try {
                    // Use raw query to handle both table name cases
                    $carImages = DB::table($tableName)
                        ->where('vehicleID', $vehicle->vehicleID)
                        ->orderBy('imgID', 'desc')
                        ->get()
                        ->map(function($item) {
                            return new \App\Models\CarImg((array)$item);
                        });
                } catch (\Exception $e) {
                    Log::warning('Failed to load car images by vehicleID: ' . $e->getMessage());
                    $carImages = collect([]);
                }
            } else {
                // If vehicleID doesn't exist yet (migration not run), try to load through VehicleDocument
                try {
                    // Try to get through VehicleDocument relationship if documentID is still a foreign key
                    $vehicleDocumentIds = \App\Models\VehicleDocument::where('vehicleID', $vehicle->vehicleID)
                        ->where(function($query) {
                            if (Schema::hasColumn('VehicleDocument', 'document_type')) {
                                $query->where('document_type', 'photo');
                            }
                        })
                        ->pluck('documentID')
                        ->filter()
                        ->toArray();
                    
                    if (!empty($vehicleDocumentIds)) {
                        // Convert documentID to integers if they are foreign keys
                        $carImages = DB::table($tableName)
                            ->whereIn('documentID', $vehicleDocumentIds)
                            ->orderBy('imgID', 'desc')
                            ->get()
                            ->map(function($item) {
                                return new \App\Models\CarImg((array)$item);
                            });
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to load car images through VehicleDocument: ' . $e->getMessage());
                    $carImages = collect([]);
                }
            }
        }
        
        // Fallback: Also load photos from VehicleDocument for backward compatibility
        $vehiclePhotos = $vehicle->documents->where('document_type', 'photo');

        // Check for due services and create notifications
        $this->checkServiceReminders($vehicle);

        // Get booked dates for calendar
        $bookedDates = [];
        foreach($vehicle->bookings as $booking) {
            if ($booking->rental_start_date && $booking->rental_end_date) {
                $start = Carbon::parse($booking->rental_start_date);
                $end = Carbon::parse($booking->rental_end_date);
                $current = $start->copy();
                while ($current <= $end) {
                    $bookedDates[] = $current->format('Y-m-d');
                    $current->addDay();
                }
            }
        }

        $activeTab = request()->get('tab', 'car-info');
        
        return view('admin.vehicles.show', [
            'vehicle' => $vehicle,
            'bookedDates' => $bookedDates,
            'activeTab' => $activeTab,
            'carImages' => $carImages,
            'vehiclePhotos' => $vehiclePhotos, // For backward compatibility
        ]);
    }

    /**
     * Show maintenance page for a vehicle.
     */
    public function maintenance(Vehicle $vehicle): View
    {
        $vehicle->load([
            'maintenances' => function($query) {
                $query->orderBy('service_date', 'desc')
                      ->with(['accompanyVehicle']);
            }
        ]);

        // Get staff users for dropdown
        $staffUsers = \App\Models\User::where(function($query) {
            $query->whereHas('staff')->orWhereHas('admin');
        })->orderBy('name')->get();

        return view('admin.vehicles.maintenance', [
            'vehicle' => $vehicle,
            'staffUsers' => $staffUsers,
        ]);
    }

    /**
     * Get available vehicles for a date range (for accompany vehicle dropdown).
     * Vehicles must be available on BOTH the start and end dates.
     */
    public function getAvailableVehicles(Request $request): \Illuminate\Http\JsonResponse
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $excludeVehicleId = $request->get('exclude_vehicle');

        if (!$startDate || !$endDate) {
            return response()->json(['vehicles' => []]);
        }

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        // Get vehicles that are available on BOTH the start AND end dates
        $availableVehicles = Vehicle::where('availability_status', '!=', 'maintenance')
            ->where('isActive', true)
            ->when($excludeVehicleId, function($query) use ($excludeVehicleId) {
                $query->where('vehicleID', '!=', $excludeVehicleId);
            })
            ->whereDoesntHave('bookings', function($query) use ($start) {
                // Not booked on start date
                $query->where('booking_status', '!=', 'Cancelled')
                      ->whereDate('rental_start_date', '<=', $start)
                      ->whereDate('rental_end_date', '>=', $start);
            })
            ->whereDoesntHave('bookings', function($query) use ($end) {
                // Not booked on end date
                $query->where('booking_status', '!=', 'Cancelled')
                      ->whereDate('rental_start_date', '<=', $end)
                      ->whereDate('rental_end_date', '>=', $end);
            })
            ->select('vehicleID', 'plate_number', 'vehicle_brand', 'vehicle_model')
            ->orderBy('plate_number')
            ->get()
            ->map(function($vehicle) {
                return [
                    'vehicleID' => $vehicle->vehicleID,
                    'plate_number' => $vehicle->plate_number,
                    'display' => $vehicle->plate_number . ' - ' . ($vehicle->vehicle_brand ?? '') . ' ' . ($vehicle->vehicle_model ?? ''),
                ];
            });

        return response()->json(['vehicles' => $availableVehicles]);
    }

    /**
     * Show fuel page for a vehicle.
     */
    public function fuel(Vehicle $vehicle): View
    {
        $vehicle->load([
            'fuels' => function($query) {
                $query->orderBy('fuel_date', 'desc')
                      ->with('handledByUser');
            }
        ]);

        // Get staff users for dropdown
        $staffUsers = \App\Models\User::where(function($query) {
            $query->whereHas('staff')->orWhereHas('admin');
        })->orderBy('name')->get();

        return view('admin.vehicles.fuel', [
            'vehicle' => $vehicle,
            'staffUsers' => $staffUsers,
        ]);
    }

    /**
     * Store a new fuel record.
     */
    public function storeFuel(Request $request, Vehicle $vehicle): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'fuel_date' => 'required|date',
            'service_type' => 'required|string|in:fuel,wash',
            'cost' => 'required|numeric|min:0',
            'receipt_img' => 'nullable|file|mimes:jpeg,jpg,png,gif,pdf|max:5120',
            'handled_by' => 'nullable|exists:user,userID',
        ]);

        try {
            // Handle receipt image upload to myportfolio public folder
            $receiptImgPath = null;
            if ($request->hasFile('receipt_img')) {
                $file = $request->file('receipt_img');
                $filename = 'fuel_receipt_' . $vehicle->vehicleID . '_' . time() . '.' . $file->getClientOriginalExtension();
                // Upload to myportfolio public folder
                $receiptImgPath = $this->uploadToGoogleDrive($file, 'fuel_receipts', $filename);
            }

            \App\Models\Fuel::create([
                'vehicleID' => $vehicle->vehicleID,
                'fuel_date' => $validated['fuel_date'],
                'service_type' => $validated['service_type'],
                'cost' => $validated['cost'],
                'receipt_img' => $receiptImgPath,
                'handled_by' => $validated['handled_by'] ?? Auth::user()->userID ?? null,
            ]);

            return redirect()->route('admin.vehicles.show', ['vehicle' => $vehicle->vehicleID, 'tab' => 'fuel'])->with('success', 'Fuel record added successfully.');
        } catch (\Exception $e) {
            return redirect()->route('admin.vehicles.show', ['vehicle' => $vehicle->vehicleID, 'tab' => 'fuel'])->withInput()->with('error', 'Failed to add fuel record: ' . $e->getMessage());
        }
    }

    /**
     * Update a fuel record.
     */
    public function updateFuel(Request $request, \App\Models\Fuel $fuel): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'fuel_date' => 'required|date',
            'service_type' => 'required|string|in:fuel,wash',
            'cost' => 'required|numeric|min:0',
            'receipt_img' => 'nullable|file|mimes:jpeg,jpg,png,gif,pdf|max:5120',
            'handled_by' => 'nullable|exists:user,userID',
        ]);

        try {
            // Handle receipt image upload to myportfolio public folder
            if ($request->hasFile('receipt_img')) {
                // Delete old image if exists
                if ($fuel->receipt_img) {
                    $this->deleteFile($fuel->receipt_img);
                }
                
                $file = $request->file('receipt_img');
                $filename = 'fuel_receipt_' . $fuel->vehicleID . '_' . time() . '.' . $file->getClientOriginalExtension();
                // Upload to myportfolio public folder
                $receiptImgPath = $this->uploadToGoogleDrive($file, 'fuel_receipts', $filename);
                $validated['receipt_img'] = $receiptImgPath;
            }

            $fuel->update($validated);

            return redirect()->route('admin.vehicles.show', ['vehicle' => $fuel->vehicleID, 'tab' => 'fuel'])->with('success', 'Fuel record updated successfully.');
        } catch (\Exception $e) {
            return redirect()->route('admin.vehicles.show', ['vehicle' => $fuel->vehicleID, 'tab' => 'fuel'])->withInput()->with('error', 'Failed to update fuel record: ' . $e->getMessage());
        }
    }

    /**
     * Delete a fuel record.
     */
    public function destroyFuel(\App\Models\Fuel $fuel): \Illuminate\Http\RedirectResponse
    {
        try {
            $vehicleId = $fuel->vehicleID;
            // Delete receipt image if exists
            if ($fuel->receipt_img) {
                $this->deleteFile($fuel->receipt_img);
            }
            
            $fuel->delete();
            return redirect()->route('admin.vehicles.show', ['vehicle' => $vehicleId, 'tab' => 'fuel'])->with('success', 'Fuel record deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('admin.vehicles.show', ['vehicle' => $fuel->vehicleID ?? $vehicleId, 'tab' => 'fuel'])->with('error', 'Failed to delete fuel record: ' . $e->getMessage());
        }
    }

    private function checkServiceReminders(Vehicle $vehicle)
    {
        $dueServices = $vehicle->maintenances()
            ->whereNotNull('next_due_date')
            ->where('next_due_date', '<=', Carbon::today()->addDays(7))
            ->where('next_due_date', '>=', Carbon::today())
            ->get();

        foreach ($dueServices as $maintenance) {
            // Check if notification already exists for this maintenance
            $existingNotification = AdminNotification::where('type', 'service_reminder')
                ->whereRaw("JSON_EXTRACT(data, '$.maintenance_id') = ?", [$maintenance->maintenanceID])
                ->where('is_read', false)
                ->first();

            if (!$existingNotification) {
                $this->createServiceReminderNotification($vehicle, $maintenance, Carbon::parse($maintenance->next_due_date));
            }
        }
    }

    public function storeMaintenance(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'service_date' => 'required|date',
            'service_type' => 'required|string|max:50',
            'description' => 'nullable|string',
            'mileage' => 'nullable|integer|min:0',
            'cost' => 'required|numeric|min:0',
            'commission_amount' => 'nullable|numeric|min:0',
            'next_due_date' => 'nullable|date',
            'service_center' => 'nullable|string|max:100',
            'maintenance_img' => 'nullable|file|mimes:jpeg,jpg,png,gif,pdf|max:5120',
            'block_start_date' => 'nullable|date',
            'block_end_date' => 'nullable|date|after_or_equal:block_start_date',
            'accompany_vehicleID' => 'nullable|exists:vehicle,vehicleID',
            'staffID' => 'nullable|exists:user,userID',
        ]);

        DB::beginTransaction();
        try {
            // Handle maintenance image upload to myportfolio public folder
            $maintenanceImgPath = null;
            if ($request->hasFile('maintenance_img')) {
                $file = $request->file('maintenance_img');
                $filename = 'maintenance_' . $vehicle->vehicleID . '_' . time() . '.' . $file->getClientOriginalExtension();
                // Upload to myportfolio public folder
                $maintenanceImgPath = $this->uploadToGoogleDrive($file, 'maintenance_images', $filename);
            }

            $maintenance = \App\Models\VehicleMaintenance::create([
                'vehicleID' => $vehicle->vehicleID,
                'service_date' => $validated['service_date'],
                'service_type' => $validated['service_type'],
                'description' => $validated['description'] ?? null,
                'mileage' => $validated['mileage'] ?? null,
                'cost' => $validated['cost'],
                'commission_amount' => $validated['commission_amount'] ?? 0,
                'next_due_date' => $validated['next_due_date'] ?? null,
                'service_center' => $validated['service_center'] ?? null,
                'maintenance_img' => $maintenanceImgPath,
                'block_start_date' => $validated['block_start_date'] ?? null,
                'block_end_date' => $validated['block_end_date'] ?? null,
                'accompany_vehicleID' => $validated['accompany_vehicleID'] ?? null,
                'staffID' => $validated['staffID'] ?? Auth::user()->userID ?? null,
            ]);

            // Handle block dates - make vehicle unavailable between block dates
            if ($validated['block_start_date'] && $validated['block_end_date']) {
                $blockStart = Carbon::parse($validated['block_start_date']);
                $blockEnd = Carbon::parse($validated['block_end_date']);
                
                // Update vehicle availability status if block dates include today
                $today = Carbon::today();
                if ($blockStart->lte($today) && $blockEnd->gte($today)) {
                    $vehicle->update(['availability_status' => 'maintenance']);
                }
            }

            // Handle accompany vehicle - make it unavailable at start and end dates only (not between)
            if ($validated['accompany_vehicleID'] && $validated['block_start_date'] && $validated['block_end_date']) {
                $accompanyVehicle = Vehicle::find($validated['accompany_vehicleID']);
                if ($accompanyVehicle) {
                    $blockStart = Carbon::parse($validated['block_start_date']);
                    $blockEnd = Carbon::parse($validated['block_end_date']);
                    $today = Carbon::today();
                    
                    // Only block on start and end dates, not between
                    // Check if start date is today or past
                    if ($blockStart->lte($today)) {
                        // Check if vehicle is already booked on start date
                        $hasBookingOnStart = $accompanyVehicle->bookings()
                            ->where('booking_status', '!=', 'Cancelled')
                            ->whereDate('rental_start_date', '<=', $blockStart)
                            ->whereDate('rental_end_date', '>=', $blockStart)
                            ->exists();
                        
                        if (!$hasBookingOnStart && $accompanyVehicle->availability_status !== 'maintenance') {
                            // Could update status temporarily, but we'll track via maintenance record
                        }
                    }
                    
                    // Check if end date is today or past
                    if ($blockEnd->lte($today)) {
                        // Check if vehicle is already booked on end date
                        $hasBookingOnEnd = $accompanyVehicle->bookings()
                            ->where('booking_status', '!=', 'Cancelled')
                            ->whereDate('rental_start_date', '<=', $blockEnd)
                            ->whereDate('rental_end_date', '>=', $blockEnd)
                            ->exists();
                        
                        if (!$hasBookingOnEnd && $accompanyVehicle->availability_status !== 'maintenance') {
                            // Could update status temporarily, but we'll track via maintenance record
                        }
                    }
                }
            }

            // Create notification for staff/admin if next_due_date is within 7 days
            if ($validated['next_due_date']) {
                $nextDue = Carbon::parse($validated['next_due_date']);
                $daysUntilDue = Carbon::today()->diffInDays($nextDue, false);
                
                if ($daysUntilDue <= 7 && $daysUntilDue >= 0) {
                    $this->createServiceReminderNotification($vehicle, $maintenance, $nextDue);
                }
            }

            DB::commit();
            return redirect()->route('admin.vehicles.show', ['vehicle' => $vehicle->vehicleID, 'tab' => 'maintenance'])->with('success', 'Maintenance record added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.vehicles.show', ['vehicle' => $vehicle->vehicleID, 'tab' => 'maintenance'])->withInput()->with('error', 'Failed to add maintenance record: ' . $e->getMessage());
        }
    }

    private function createServiceReminderNotification(Vehicle $vehicle, $maintenance, $dueDate)
    {
        $message = "Service reminder: {$vehicle->vehicle_brand} {$vehicle->vehicle_model} ({$vehicle->plate_number}) - {$maintenance->service_type} due on " . $dueDate->format('d M Y');
        
        // Create notification for all admin users
        AdminNotification::create([
            'type' => 'service_reminder',
            'notifiable_type' => 'admin',
            'notifiable_id' => null,
            'user_id' => Auth::id(),
            'message' => $message,
            'data' => [
                'vehicle_id' => $vehicle->vehicleID,
                'maintenance_id' => $maintenance->maintenanceID,
                'due_date' => $dueDate->toDateString(),
            ],
            'is_read' => false,
        ]);
    }

    public function destroyMaintenance(\App\Models\VehicleMaintenance $maintenance)
    {
        $vehicleId = $maintenance->vehicleID;
        $maintenance->delete();
        return redirect()->route('admin.vehicles.show', ['vehicle' => $vehicleId, 'tab' => 'maintenance'])->with('success', 'Maintenance record deleted successfully.');
    }

    public function storeDocument(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'document_type' => 'required|string|in:insurance,grant,roadtax,contract',
            'file' => 'required|mimes:jpg,jpeg,png,gif,pdf|max:5120', // 5MB max, accepts images and PDFs
        ]);

        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        
        // Upload to myportfolio public folder (grant, roadtax, contract, insurance)
        $fileId = $this->uploadToGoogleDrive($file, 'vehicle_documents/' . $validated['document_type'], $fileName);

        // Check if document type column exists, if not use a different approach
        $documentData = [
            'vehicleID' => $vehicle->vehicleID,
            'fileURL' => $fileId, // Store Google Drive file ID
            'upload_date' => Carbon::today(),
        ];

        // Add document_type if column exists
        if (Schema::hasColumn('VehicleDocument', 'document_type')) {
            $documentData['document_type'] = $validated['document_type'];
        }

        $document = \App\Models\VehicleDocument::create($documentData);

        return redirect()->back()->with('success', 'Document uploaded successfully to Google Drive.');
    }

    public function storePhoto(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'photo' => 'required|image|max:5120', // 5MB max
            'photo_type' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();
            
            $file = $request->file('photo');
            $fileName = time() . '_' . $file->getClientOriginalName();
            
            // Upload car images to myportfolio public storage folder (C:\xampp\htdocs\myportfolio\public\storage\vehicle_photos)
            // Store directly in public/storage/vehicle_photos (bypass uploads/ normalization)
            $destinationPath = public_path('storage/vehicle_photos');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            $file->move($destinationPath, $fileName);
            $filePath = 'storage/vehicle_photos/' . $fileName;
            $fileUrl = asset($filePath);
            $uploadResult = ['fileId' => $filePath, 'fileUrl' => $fileUrl];
            
            $fileId = $uploadResult['fileId'];  // File path relative to public folder (e.g., storage/vehicle_photos/xxx.jpg)
            $fileUrl = $uploadResult['fileUrl']; // Full URL to the file

            // Determine table name (check both Car_Img and car_img)
            $tableName = Schema::hasTable('Car_Img') ? 'Car_Img' : (Schema::hasTable('car_img') ? 'car_img' : 'Car_Img');
            
            // Check if vehicleID column exists, if not, add it
            $hasVehicleIdColumn = Schema::hasColumn($tableName, 'vehicleID');
            if (!$hasVehicleIdColumn) {
                try {
                    // First, modify documentID to TEXT if needed
                    try {
                        DB::statement("ALTER TABLE `{$tableName}` MODIFY COLUMN `documentID` TEXT NULL");
                    } catch (\Exception $e) {
                        Log::warning('Failed to modify documentID column: ' . $e->getMessage());
                    }
                    
                    // Add vehicleID column
                    DB::statement("ALTER TABLE `{$tableName}` ADD COLUMN `vehicleID` INT UNSIGNED NULL AFTER `imgID`");
                    $hasVehicleIdColumn = true;
                    Log::info("Added vehicleID column to {$tableName} table");
                } catch (\Exception $e) {
                    Log::warning('Failed to add vehicleID column: ' . $e->getMessage());
                    // Continue without vehicleID column
                }
            }
            
            // Create Car_Img record directly with Google Drive URL stored in documentID
            // Note: documentID should now be TEXT to store URL, if migration ran or we modified it above
            $carImgData = [
                'imageType' => $validated['photo_type'] ?? 'other',
                'img_description' => $validated['description'] ?? null,
            ];
            
            // Add vehicleID if column exists
            if ($hasVehicleIdColumn) {
                $carImgData['vehicleID'] = $vehicle->vehicleID;
            }
            
            // Add documentID - try as TEXT first, if that fails, try as integer (legacy)
            try {
                $carImgData['documentID'] = $fileUrl; // Store Google Drive URL as TEXT
                if ($hasVehicleIdColumn) {
                    \App\Models\CarImg::create($carImgData);
                } else {
                    DB::table($tableName)->insert($carImgData);
                }
            } catch (\Exception $e) {
                // If documentID is still INT, we need to store in VehicleDocument and reference that
                Log::warning('Failed to insert with URL in documentID (might be INT): ' . $e->getMessage());
                try {
                    // Store in VehicleDocument first and get its ID
                    $vehicleDoc = \App\Models\VehicleDocument::create([
                        'vehicleID' => $vehicle->vehicleID,
                        'fileURL' => $fileUrl,
                        'upload_date' => Carbon::today(),
                        'document_type' => 'photo',
                    ]);
                    
                    // Use VehicleDocument ID instead
                    unset($carImgData['documentID']);
                    $carImgData['documentID'] = $vehicleDoc->documentID; // Use integer ID
                    
                    if ($hasVehicleIdColumn) {
                        $carImgData['vehicleID'] = $vehicle->vehicleID;
                    }
                    
                    DB::table($tableName)->insert($carImgData);
                } catch (\Exception $e2) {
                    Log::error('Failed to insert into Car_Img table: ' . $e2->getMessage());
                    throw $e2;
                }
            }

            // Also create VehicleDocument record to maintain vehicle relationship for reference
            // This helps link the image to the vehicle even if Car_Img structure changes
            try {
                $documentData = [
                    'vehicleID' => $vehicle->vehicleID,
                    'fileURL' => $fileUrl, // Store Google Drive URL
                    'upload_date' => Carbon::today(),
                ];

                if (Schema::hasColumn('VehicleDocument', 'document_type')) {
                    $documentData['document_type'] = 'photo';
                }

                \App\Models\VehicleDocument::create($documentData);
            } catch (\Exception $e) {
                Log::warning('Failed to create VehicleDocument record: ' . $e->getMessage());
                // Continue even if VehicleDocument creation fails
            }

            DB::commit();
            
            return redirect()->route('admin.vehicles.show', ['vehicle' => $vehicle->vehicleID, 'tab' => 'car-photo'])->with('success', 'Photo uploaded successfully to Google Drive folder and saved to car_img table. URL stored in VehicleDocument.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Photo upload failed: ' . $e->getMessage());
            return redirect()->route('admin.vehicles.show', ['vehicle' => $vehicle->vehicleID, 'tab' => 'car-photo'])->with('error', 'Failed to upload photo: ' . $e->getMessage());
        }
    }

    public function destroyDocument(\App\Models\VehicleDocument $document)
    {
        if ($document->fileURL && \Storage::disk('public')->exists($document->fileURL)) {
            \Storage::disk('public')->delete($document->fileURL);
        }
        
        // If fileURL is a Google Drive file ID or URL, delete from Google Drive
        if ($document->fileURL && (strpos($document->fileURL, 'drive.google.com') !== false || !strpos($document->fileURL, '/') && !strpos($document->fileURL, '\\'))) {
            try {
                $this->deleteFile($document->fileURL);
            } catch (\Exception $e) {
                Log::warning('Failed to delete from Google Drive: ' . $e->getMessage());
            }
        }
        
        $document->delete();
        return redirect()->back()->with('success', 'Document deleted successfully.');
    }

    /**
     * Delete a car image from car_img table
     */
    public function destroyPhoto($imgId)
    {
        try {
            DB::beginTransaction();
            
            // Try to find in car_img table
            $carImg = null;
            
            // Try using CarImg model first
            try {
                $carImg = \App\Models\CarImg::find($imgId);
            } catch (\Exception $e) {
                Log::warning('CarImg model find failed: ' . $e->getMessage());
            }
            
            // If not found, try direct DB query
            if (!$carImg) {
                try {
                    $carImgRecord = DB::table('Car_Img')->where('imgID', $imgId)->first();
                    if ($carImgRecord) {
                        $carImg = (object) $carImgRecord;
                    } else {
                        $carImgRecord = DB::table('car_img')->where('imgID', $imgId)->first();
                        if ($carImgRecord) {
                            $carImg = (object) $carImgRecord;
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Direct DB query failed: ' . $e->getMessage());
                }
            }

            if (!$carImg) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Photo not found.');
            }

            // Get vehicleID before deletion
            $vehicleId = null;
            if ($carImg instanceof \App\Models\CarImg) {
                $vehicleId = $carImg->vehicleID;
            } elseif (is_object($carImg) && isset($carImg->vehicleID)) {
                $vehicleId = $carImg->vehicleID;
            } elseif (is_array($carImg) && isset($carImg['vehicleID'])) {
                $vehicleId = $carImg['vehicleID'];
            }

            // If vehicleID not found in carImg, try to get from VehicleDocument via documentID
            if (!$vehicleId) {
                $documentId = is_object($carImg) && isset($carImg->documentID) 
                    ? $carImg->documentID 
                    : (is_array($carImg) ? ($carImg['documentID'] ?? null) : null);
                
                if ($documentId) {
                    // Try to find vehicle from VehicleDocument
                    $vehicleDoc = \App\Models\VehicleDocument::where('documentID', $documentId)
                        ->orWhere('fileURL', $documentId)
                        ->first();
                    if ($vehicleDoc) {
                        $vehicleId = $vehicleDoc->vehicleID;
                    }
                }
            }

            // Get the documentID (Google Drive URL) before deletion
            $documentId = is_object($carImg) && isset($carImg->documentID) 
                ? $carImg->documentID 
                : (is_array($carImg) ? ($carImg['documentID'] ?? null) : null);

            // Delete from Google Drive if documentID is a Google Drive URL or file ID
            if ($documentId) {
                try {
                    $driveService = new \App\Services\GoogleDriveService();
                    // deleteFile method now handles URL extraction
                    $driveService->deleteFile($documentId);
                } catch (\Exception $e) {
                    Log::warning('Failed to delete from Google Drive: ' . $e->getMessage());
                    // Continue with database deletion even if Google Drive delete fails
                }
            }

            // Delete from database
            if ($carImg instanceof \App\Models\CarImg) {
                $carImg->delete();
            } else {
                // Direct DB delete
                $deleted = false;
                if (Schema::hasTable('Car_Img')) {
                    $deleted = DB::table('Car_Img')->where('imgID', $imgId)->delete() > 0;
                }
                if (!$deleted && Schema::hasTable('car_img')) {
                    DB::table('car_img')->where('imgID', $imgId)->delete();
                }
            }

            DB::commit();
            
            // Redirect to car-photo tab if vehicleId is available, otherwise back
            if ($vehicleId) {
                return redirect()->route('admin.vehicles.show', ['vehicle' => $vehicleId, 'tab' => 'car-photo'])->with('success', 'Photo deleted successfully.');
            }
            return redirect()->back()->with('success', 'Photo deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete photo: ' . $e->getMessage());
            
            // Try to get vehicleId from request or carImg for error redirect
            $errorVehicleId = $vehicleId ?? null;
            if (!$errorVehicleId) {
                // Try to get from request if available
                try {
                    $carImg = \App\Models\CarImg::find($imgId);
                    if ($carImg && isset($carImg->vehicleID)) {
                        $errorVehicleId = $carImg->vehicleID;
                    }
                } catch (\Exception $e2) {
                    // Ignore
                }
            }
            
            if ($errorVehicleId) {
                return redirect()->route('admin.vehicles.show', ['vehicle' => $errorVehicleId, 'tab' => 'car-photo'])->with('error', 'Failed to delete photo: ' . $e->getMessage());
            }
            return redirect()->back()->with('error', 'Failed to delete photo: ' . $e->getMessage());
        }
    }

    public function createCar(): View
    {
        return view('admin.vehicles.create-car');
    }

    public function createMotorcycle(): View
    {
        return view('admin.vehicles.create-motorcycle');
    }

    public function createOther(): View
    {
        return view('admin.vehicles.create-other');
    }

    public function storeCar(Request $request)
    {
        $validated = $request->validate([
            'vehicle_brand' => 'required|string|max:255',
            'vehicle_model' => 'required|string|max:255',
            'plate_number' => 'required|string|max:20|unique:vehicle,plate_number',
            'manufacturing_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'color' => 'nullable|string|max:50',
            'engineCapacity' => 'nullable|numeric|min:0',
            'rental_price' => 'required|numeric|min:0',
            'seating_capacity' => 'required|integer|min:1|max:50',
            'transmission' => 'required|string|in:Manual,Automatic',
            'car_type' => 'required|string|max:50',
            'availability_status' => 'required|string|in:available,rented,maintenance,unavailable',
        ]);

        DB::transaction(function() use ($validated) {
            $vehicle = Vehicle::create([
                'vehicle_brand' => $validated['vehicle_brand'],
                'vehicle_model' => $validated['vehicle_model'],
                'plate_number' => $validated['plate_number'],
                'manufacturing_year' => $validated['manufacturing_year'] ?? null,
                'color' => $validated['color'] ?? null,
                'engineCapacity' => $validated['engineCapacity'] ?? null,
                'rental_price' => $validated['rental_price'],
                'vehicleType' => 'Car',
                'availability_status' => $validated['availability_status'],
                'created_date' => Carbon::today(),
                'isActive' => true,
            ]);

            \App\Models\Car::create([
                'vehicleID' => $vehicle->vehicleID,
                'seating_capacity' => $validated['seating_capacity'],
                'transmission' => $validated['transmission'],
                'car_type' => $validated['car_type'],
            ]);
        });

        return redirect()->route('admin.vehicles.cars')->with('success', 'Car created successfully.');
    }

    public function storeMotorcycle(Request $request)
    {
        $validated = $request->validate([
            'vehicle_brand' => 'required|string|max:255',
            'vehicle_model' => 'required|string|max:255',
            'plate_number' => 'required|string|max:20|unique:vehicle,plate_number',
            'manufacturing_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'color' => 'nullable|string|max:50',
            'engineCapacity' => 'nullable|numeric|min:0',
            'rental_price' => 'required|numeric|min:0',
            'motor_type' => 'required|string|max:50',
            'availability_status' => 'required|string|in:available,rented,maintenance,unavailable',
        ]);

        DB::transaction(function() use ($validated) {
            $vehicle = Vehicle::create([
                'vehicle_brand' => $validated['vehicle_brand'],
                'vehicle_model' => $validated['vehicle_model'],
                'plate_number' => $validated['plate_number'],
                'manufacturing_year' => $validated['manufacturing_year'] ?? null,
                'color' => $validated['color'] ?? null,
                'engineCapacity' => $validated['engineCapacity'] ?? null,
                'rental_price' => $validated['rental_price'],
                'vehicleType' => 'Motorcycle',
                'availability_status' => $validated['availability_status'],
                'created_date' => Carbon::today(),
                'isActive' => true,
            ]);

            \App\Models\Motorcycle::create([
                'vehicleID' => $vehicle->vehicleID,
                'motor_type' => $validated['motor_type'],
            ]);
        });

        return redirect()->route('admin.vehicles.motorcycles')->with('success', 'Motorcycle created successfully.');
    }

    public function storeOther(Request $request)
    {
        $validated = $request->validate([
            'vehicle_brand' => 'required|string|max:255',
            'vehicle_model' => 'required|string|max:255',
            'plate_number' => 'required|string|max:20|unique:vehicle,plate_number',
            'manufacturing_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'color' => 'nullable|string|max:50',
            'engineCapacity' => 'nullable|numeric|min:0',
            'rental_price' => 'required|numeric|min:0',
            'availability_status' => 'required|string|in:available,rented,maintenance,unavailable',
            'vehicleType' => 'nullable|string|max:50',
        ]);

        DB::transaction(function() use ($validated) {
            Vehicle::create([
                'vehicle_brand' => $validated['vehicle_brand'],
                'vehicle_model' => $validated['vehicle_model'],
                'plate_number' => $validated['plate_number'],
                'manufacturing_year' => $validated['manufacturing_year'] ?? null,
                'color' => $validated['color'] ?? null,
                'engineCapacity' => $validated['engineCapacity'] ?? null,
                'rental_price' => $validated['rental_price'],
                'vehicleType' => $validated['vehicleType'] ?? 'Other',
                'availability_status' => $validated['availability_status'],
                'created_date' => Carbon::today(),
                'isActive' => true,
            ]);
        });

        return redirect()->route('admin.vehicles.others', ['tab' => 'vehicle'])->with('success', 'Vehicle created successfully.');
    }

    public function editCar(Vehicle $vehicle): View
    {
        $car = $vehicle->car;
        if (!$car) {
            abort(404, 'Car not found');
        }
        return view('admin.vehicles.edit-car', [
            'vehicle' => $vehicle,
            'car' => $car,
        ]);
    }

    public function editMotorcycle(Vehicle $vehicle): View
    {
        $motorcycle = $vehicle->motorcycle;
        if (!$motorcycle) {
            abort(404, 'Motorcycle not found');
        }
        return view('admin.vehicles.edit-motorcycle', [
            'vehicle' => $vehicle,
            'motorcycle' => $motorcycle,
        ]);
    }

    public function updateCar(Request $request, Vehicle $vehicle)
    {
        $car = $vehicle->car;
        if (!$car) {
            abort(404, 'Car not found');
        }

        $validated = $request->validate([
            'vehicle_brand' => 'required|string|max:255',
            'vehicle_model' => 'required|string|max:255',
            'plate_number' => 'required|string|max:20|unique:vehicle,plate_number,' . $vehicle->vehicleID . ',vehicleID',
            'manufacturing_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'color' => 'nullable|string|max:50',
            'engineCapacity' => 'nullable|numeric|min:0',
            'rental_price' => 'required|numeric|min:0',
            'seating_capacity' => 'required|integer|min:1|max:50',
            'transmission' => 'required|string|in:Manual,Automatic',
            'car_type' => 'required|string|max:50',
        ]);

        $vehicle->update([
            'vehicle_brand' => $validated['vehicle_brand'],
            'vehicle_model' => $validated['vehicle_model'],
            'plate_number' => $validated['plate_number'],
            'manufacturing_year' => $validated['manufacturing_year'] ?? null,
            'color' => $validated['color'] ?? null,
            'engineCapacity' => $validated['engineCapacity'] ?? null,
            'rental_price' => $validated['rental_price'],
        ]);

        $car->update([
            'seating_capacity' => $validated['seating_capacity'],
            'transmission' => $validated['transmission'],
            'car_type' => $validated['car_type'],
        ]);

        return redirect()->route('admin.vehicles.cars')->with('success', 'Car updated successfully.');
    }

    public function updateMotorcycle(Request $request, Vehicle $vehicle)
    {
        $motorcycle = $vehicle->motorcycle;
        if (!$motorcycle) {
            abort(404, 'Motorcycle not found');
        }

        $validated = $request->validate([
            'vehicle_brand' => 'required|string|max:255',
            'vehicle_model' => 'required|string|max:255',
            'plate_number' => 'required|string|max:20|unique:vehicle,plate_number,' . $vehicle->vehicleID . ',vehicleID',
            'manufacturing_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'color' => 'nullable|string|max:50',
            'engineCapacity' => 'nullable|numeric|min:0',
            'rental_price' => 'required|numeric|min:0',
            'motor_type' => 'required|string|max:50',
        ]);

        $vehicle->update([
            'vehicle_brand' => $validated['vehicle_brand'],
            'vehicle_model' => $validated['vehicle_model'],
            'plate_number' => $validated['plate_number'],
            'manufacturing_year' => $validated['manufacturing_year'] ?? null,
            'color' => $validated['color'] ?? null,
            'engineCapacity' => $validated['engineCapacity'] ?? null,
            'rental_price' => $validated['rental_price'],
        ]);

        $motorcycle->update([
            'motor_type' => $validated['motor_type'],
        ]);

        return redirect()->route('admin.vehicles.motorcycles')->with('success', 'Motorcycle updated successfully.');
    }

    public function editOther(Vehicle $vehicle): View
    {
        // Ensure this is an "other" vehicle (not a car or motorcycle)
        if ($vehicle->car || $vehicle->motorcycle) {
            abort(404, 'This vehicle is not an "other" type vehicle.');
        }
        return view('admin.vehicles.edit-other', [
            'vehicle' => $vehicle,
        ]);
    }

    public function updateOther(Request $request, Vehicle $vehicle)
    {
        // Ensure this is an "other" vehicle (not a car or motorcycle)
        if ($vehicle->car || $vehicle->motorcycle) {
            abort(404, 'This vehicle is not an "other" type vehicle.');
        }

        $validated = $request->validate([
            'vehicle_brand' => 'required|string|max:255',
            'vehicle_model' => 'required|string|max:255',
            'plate_number' => 'required|string|max:20|unique:vehicle,plate_number,' . $vehicle->vehicleID . ',vehicleID',
            'manufacturing_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'color' => 'nullable|string|max:50',
            'engineCapacity' => 'nullable|numeric|min:0',
            'rental_price' => 'required|numeric|min:0',
            'availability_status' => 'required|string|in:available,rented,maintenance,unavailable',
            'isActive' => 'nullable|boolean',
        ]);

        $vehicle->update([
            'vehicle_brand' => $validated['vehicle_brand'],
            'vehicle_model' => $validated['vehicle_model'],
            'plate_number' => $validated['plate_number'],
            'manufacturing_year' => $validated['manufacturing_year'] ?? null,
            'color' => $validated['color'] ?? null,
            'engineCapacity' => $validated['engineCapacity'] ?? null,
            'rental_price' => $validated['rental_price'],
            'availability_status' => $validated['availability_status'],
            'isActive' => $request->has('isActive') ? ($validated['isActive'] ?? true) : $vehicle->isActive,
        ]);

        return redirect()->route('admin.vehicles.others', ['tab' => 'vehicle'])->with('success', 'Vehicle updated successfully.');
    }

    public function updateStatus(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'availability_status' => 'required|string|in:available,rented,maintenance,unavailable',
        ]);

        $vehicle->update([
            'availability_status' => $validated['availability_status'],
        ]);

        return response()->json(['success' => true, 'message' => 'Status updated successfully.']);
    }

    public function destroyCar(Vehicle $vehicle)
    {
        if ($vehicle->bookings()->count() > 0) {
            if (request()->expectsJson() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Cannot delete car with existing bookings.'], 422);
            }
            return redirect()->back()->with('error', 'Cannot delete car with existing bookings.');
        }

        try {
            $vehicle->car()->delete();
            $vehicle->delete();

            if (request()->expectsJson() || request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Car deleted successfully.']);
            }
            return redirect()->route('admin.vehicles.cars')->with('success', 'Car deleted successfully.');
        } catch (\Exception $e) {
            if (request()->expectsJson() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to delete car: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Failed to delete car: ' . $e->getMessage());
        }
    }

    public function destroyMotorcycle(Vehicle $vehicle)
    {
        if ($vehicle->bookings()->count() > 0) {
            if (request()->expectsJson() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Cannot delete motorcycle with existing bookings.'], 422);
            }
            return redirect()->back()->with('error', 'Cannot delete motorcycle with existing bookings.');
        }

        try {
            $vehicle->motorcycle()->delete();
            $vehicle->delete();

            if (request()->expectsJson() || request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Motorcycle deleted successfully.']);
            }
            return redirect()->route('admin.vehicles.motorcycles')->with('success', 'Motorcycle deleted successfully.');
        } catch (\Exception $e) {
            if (request()->expectsJson() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to delete motorcycle: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Failed to delete motorcycle: ' . $e->getMessage());
        }
    }

    public function exportCarsPdf(Request $request)
    {
        $query = $this->buildCarsQuery($request);
        $cars = $query->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.vehicles.export-cars-pdf', [
            'cars' => $cars,
            'filters' => $request->all(),
        ]);

        return $pdf->download('cars-export-' . date('Y-m-d') . '.pdf');
    }

    public function exportCarsExcel(Request $request)
    {
        $query = $this->buildCarsQuery($request);
        $cars = $query->get();

        $data = $cars->map(function($car) {
            return [
                'Vehicle ID' => $car->vehicleID,
                'Brand' => $car->vehicle_brand ?? 'N/A',
                'Model' => $car->vehicle_model ?? 'N/A',
                'Plate Number' => $car->plate_number ?? 'N/A',
                'Seating Capacity' => $car->seating_capacity ?? 'N/A',
                'Transmission' => $car->transmission ?? 'N/A',
                'Car Type' => $car->car_type ?? 'N/A',
                'Rental Price' => number_format($car->rental_price ?? 0, 2),
                'Status' => $car->availability_status ?? 'N/A',
            ];
        });

        $filename = 'cars-export-' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            if ($data->isNotEmpty()) {
                fputcsv($file, array_keys($data->first()));
            }
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportMotorcyclesPdf(Request $request)
    {
        $query = $this->buildMotorcyclesQuery($request);
        $motorcycles = $query->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.vehicles.export-motorcycles-pdf', [
            'motorcycles' => $motorcycles,
            'filters' => $request->all(),
        ]);

        return $pdf->download('motorcycles-export-' . date('Y-m-d') . '.pdf');
    }

    public function exportMotorcyclesExcel(Request $request)
    {
        $query = $this->buildMotorcyclesQuery($request);
        $motorcycles = $query->get();

        $data = $motorcycles->map(function($motorcycle) {
            return [
                'Vehicle ID' => $motorcycle->vehicleID,
                'Brand' => $motorcycle->vehicle_brand ?? 'N/A',
                'Model' => $motorcycle->vehicle_model ?? 'N/A',
                'Plate Number' => $motorcycle->plate_number ?? 'N/A',
                'Motor Type' => $motorcycle->motor_type ?? 'N/A',
                'Rental Price' => number_format($motorcycle->rental_price ?? 0, 2),
                'Status' => $motorcycle->availability_status ?? 'N/A',
            ];
        });

        $filename = 'motorcycles-export-' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            if ($data->isNotEmpty()) {
                fputcsv($file, array_keys($data->first()));
            }
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function buildCarsQuery(Request $request)
    {
        $query = \App\Models\Car::with('vehicle')
            ->join('vehicle', 'car.vehicleID', '=', 'vehicle.vehicleID')
            ->select('car.*', 'vehicle.vehicle_brand', 'vehicle.vehicle_model', 'vehicle.plate_number', 'vehicle.availability_status', 'vehicle.rental_price', 'vehicle.created_date', 'vehicle.vehicleID');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('vehicle.vehicle_brand', 'like', "%{$search}%")
                  ->orWhere('vehicle.vehicle_model', 'like', "%{$search}%")
                  ->orWhere('vehicle.plate_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('filter_brand')) {
            $query->where('vehicle.vehicle_brand', $request->filter_brand);
        }
        if ($request->filled('filter_model')) {
            $query->where('vehicle.vehicle_model', $request->filter_model);
        }
        if ($request->filled('filter_seating')) {
            $query->where('car.seating_capacity', $request->filter_seating);
        }
        if ($request->filled('filter_transmission')) {
            $query->where('car.transmission', $request->filter_transmission);
        }
        if ($request->filled('filter_car_type')) {
            $query->where('car.car_type', $request->filter_car_type);
        }
        if ($request->filled('filter_status')) {
            $query->where('vehicle.availability_status', $request->filter_status);
        }

        return $query->orderBy('vehicle.vehicleID', 'ASC');
    }

    private function buildMotorcyclesQuery(Request $request)
    {
        $query = \App\Models\Motorcycle::with('vehicle')
            ->join('vehicle', 'motorcycle.vehicleID', '=', 'vehicle.vehicleID')
            ->select('motorcycle.*', 'vehicle.vehicle_brand', 'vehicle.vehicle_model', 'vehicle.plate_number', 'vehicle.availability_status', 'vehicle.rental_price', 'vehicle.created_date', 'vehicle.vehicleID');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('vehicle.vehicle_brand', 'like', "%{$search}%")
                  ->orWhere('vehicle.vehicle_model', 'like', "%{$search}%")
                  ->orWhere('vehicle.plate_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('filter_brand')) {
            $query->where('vehicle.vehicle_brand', $request->filter_brand);
        }
        if ($request->filled('filter_model')) {
            $query->where('vehicle.vehicle_model', $request->filter_model);
        }
        if ($request->filled('filter_motor_type')) {
            $query->where('motorcycle.motor_type', $request->filter_motor_type);
        }
        if ($request->filled('filter_status')) {
            $query->where('vehicle.availability_status', $request->filter_status);
        }

        return $query->orderBy('vehicle.vehicleID', 'ASC');
    }

    public function exportAllPdf(Request $request)
    {
        $query = $this->buildAllVehiclesQuery($request);
        $vehicles = $query->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.vehicles.export-all-pdf', [
            'vehicles' => $vehicles,
            'filters' => $request->all(),
        ]);

        return $pdf->download('vehicles-export-' . date('Y-m-d') . '.pdf');
    }

    public function exportAllExcel(Request $request)
    {
        $query = $this->buildAllVehiclesQuery($request);
        $vehicles = $query->get();

        $data = $vehicles->map(function($vehicle) {
            $vehicleType = 'Other';
            if ($vehicle->car) {
                $vehicleType = 'Car';
            } elseif ($vehicle->motorcycle) {
                $vehicleType = 'Motorcycle';
            }
            
            return [
                'Vehicle ID' => $vehicle->vehicleID,
                'Brand' => $vehicle->vehicle_brand ?? 'N/A',
                'Model' => $vehicle->vehicle_model ?? 'N/A',
                'Plate Number' => $vehicle->plate_number ?? 'N/A',
                'Type' => $vehicleType,
                'Created Date' => $vehicle->created_date ? \Carbon\Carbon::parse($vehicle->created_date)->format('Y-m-d') : 'N/A',
                'Manufacturing Year' => $vehicle->manufacturing_year ?? 'N/A',
                'Engine Capacity' => $vehicle->engineCapacity ? number_format($vehicle->engineCapacity, 2) . 'L' : 'N/A',
                'Rental Price' => number_format($vehicle->rental_price ?? 0, 2),
                'Is Active' => ($vehicle->isActive ?? false) ? 'Yes' : 'No',
            ];
        });

        $filename = 'vehicles-export-' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            if ($data->isNotEmpty()) {
                fputcsv($file, array_keys($data->first()));
            }
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function buildAllVehiclesQuery(Request $request)
    {
        $query = Vehicle::with(['car', 'motorcycle']);
        
        $filterBrand = $request->get('filter_brand');
        $filterModel = $request->get('filter_model');
        $filterType = $request->get('filter_type');
        $filterIsActive = $request->get('filter_isactive');
        
        if ($filterBrand) {
            $query->where('vehicle_brand', $filterBrand);
        }
        if ($filterModel) {
            $query->where('vehicle_model', $filterModel);
        }
        if ($filterType) {
            if ($filterType === 'car') {
                $query->whereHas('car');
            } elseif ($filterType === 'motor') {
                $query->whereHas('motorcycle');
            } elseif ($filterType === 'other') {
                $query->whereDoesntHave('car')->whereDoesntHave('motorcycle');
            }
        }
        if ($filterIsActive !== null && $filterIsActive !== '') {
            $query->where('isActive', $filterIsActive == 1);
        }
        
        return $query->orderBy('vehicleID', 'ASC');
    }

    public function destroy(Vehicle $vehicle)
    {
        if ($vehicle->bookings()->count() > 0) {
            if (request()->expectsJson() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Cannot delete vehicle with existing bookings.'], 422);
            }
            return redirect()->back()->with('error', 'Cannot delete vehicle with existing bookings.');
        }

        try {
            // Delete related records if they exist
            if ($vehicle->car) {
                $vehicle->car()->delete();
            }
            if ($vehicle->motorcycle) {
                $vehicle->motorcycle()->delete();
            }
            $vehicle->delete();

            if (request()->expectsJson() || request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Vehicle deleted successfully.']);
            }
            return redirect()->back()->with('success', 'Vehicle deleted successfully.');
        } catch (\Exception $e) {
            if (request()->expectsJson() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to delete vehicle: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Failed to delete vehicle: ' . $e->getMessage());
        }
    }

    public function updateOwner(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'ic_no' => 'required|string|max:20',
            'owner_name' => 'nullable|string|max:100',
            'contact_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'bankname' => 'nullable|string|max:50',
            'bank_acc_number' => 'nullable|string|max:30',
            'registration_date' => 'nullable|date',
            'leasing_price' => 'nullable|numeric|min:0',
            'leasing_due_date' => 'nullable|date',
            'isActive' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            // Create or update PersonDetails
            \App\Models\PersonDetails::updateOrCreate(
                ['ic_no' => $validated['ic_no']],
                ['fullname' => $validated['owner_name'] ?? 'Unknown']
            );

            // Get or create OwnerCar
            $owner = \App\Models\OwnerCar::firstOrCreate(
                ['ic_no' => $validated['ic_no']],
                [
                    'contact_number' => $validated['contact_number'] ?? null,
                    'email' => $validated['email'] ?? null,
                    'bankname' => $validated['bankname'] ?? null,
                    'bank_acc_number' => $validated['bank_acc_number'] ?? null,
                    'registration_date' => $validated['registration_date'] ?? now(),
                    'leasing_price' => $validated['leasing_price'] ?? null,
                    'leasing_due_date' => $validated['leasing_due_date'] ?? null,
                    'isActive' => $request->has('isActive') ? ($validated['isActive'] ?? true) : true,
                ]
            );

            // Always update owner fields (even if just created, in case form has more updated data)
            $owner->update([
                'contact_number' => $validated['contact_number'] ?? $owner->contact_number,
                'email' => $validated['email'] ?? $owner->email,
                'bankname' => $validated['bankname'] ?? $owner->bankname,
                'bank_acc_number' => $validated['bank_acc_number'] ?? $owner->bank_acc_number,
                'registration_date' => $validated['registration_date'] ?? $owner->registration_date,
                'leasing_price' => $validated['leasing_price'] ?? $owner->leasing_price,
                'leasing_due_date' => $validated['leasing_due_date'] ?? $owner->leasing_due_date,
                'isActive' => $request->has('isActive') ? ($validated['isActive'] ?? true) : $owner->isActive,
            ]);

            // Update vehicle to link to owner
            $vehicle->update(['ownerID' => $owner->ownerID]);

            DB::commit();

            return redirect()->route('admin.vehicles.show', ['vehicle' => $vehicle->vehicleID, 'tab' => 'owner-info'])->with('success', 'Owner information updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.vehicles.show', ['vehicle' => $vehicle->vehicleID, 'tab' => 'owner-info'])->withInput()->with('error', 'Failed to update owner: ' . $e->getMessage());
        }
    }

    public function uploadOwnerLicense(Request $request, Vehicle $vehicle)
    {
        $request->validate([
            'license_img' => 'required|file|mimes:jpeg,jpg,png,gif,pdf|max:5120',
        ]);

        if (!$vehicle->owner) {
            return redirect()->back()->with('error', 'No owner associated with this vehicle.');
        }

        try {
            $file = $request->file('license_img');
            $filename = 'owner_license_' . $vehicle->owner->ownerID . '_' . time() . '.' . $file->getClientOriginalExtension();
            
            // Upload owner license to myportfolio public folder
            $uploadResult = $this->uploadToGoogleDriveWithUrl($file, 'owner_license', $filename);
            
            $fileId = $uploadResult['fileId'];
            $fileUrl = $uploadResult['fileUrl'];

            $vehicle->owner->update([
                'license_img' => $fileUrl, // Store URL
            ]);

            return redirect()->route('admin.vehicles.show', ['vehicle' => $vehicle->vehicleID, 'tab' => 'owner-info'])->with('success', 'Owner license uploaded successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to upload license: ' . $e->getMessage());
        }
    }

    public function uploadOwnerIc(Request $request, Vehicle $vehicle)
    {
        $request->validate([
            'ic_img' => 'required|file|mimes:jpeg,jpg,png,gif,pdf|max:5120',
        ]);

        if (!$vehicle->owner) {
            return redirect()->back()->with('error', 'No owner associated with this vehicle.');
        }

        try {
            $file = $request->file('ic_img');
            $filename = 'owner_ic_' . $vehicle->owner->ownerID . '_' . time() . '.' . $file->getClientOriginalExtension();
            
            // Upload owner IC to myportfolio public folder
            $uploadResult = $this->uploadToGoogleDriveWithUrl($file, 'owner_ic', $filename);
            
            $fileId = $uploadResult['fileId'];
            $fileUrl = $uploadResult['fileUrl'];

            // Check if ic_img column exists, if not, add it
            if (!Schema::hasColumn('ownercar', 'ic_img')) {
                try {
                    Schema::table('ownercar', function (Blueprint $table) {
                        $table->string('ic_img', 500)->nullable()->after('license_img');
                    });
                } catch (\Exception $e) {
                    Log::warning('Failed to add ic_img column: ' . $e->getMessage());
                    // Try using raw SQL
                    try {
                        DB::statement('ALTER TABLE `ownercar` ADD COLUMN `ic_img` VARCHAR(500) NULL AFTER `license_img`');
                    } catch (\Exception $e2) {
                        Log::error('Failed to add ic_img column using raw SQL: ' . $e2->getMessage());
                        return redirect()->back()->with('error', 'Failed to add ic_img column to database. Please run migration manually.');
                    }
                }
            }

            // Update OwnerCar table with IC image
            try {
                $vehicle->owner->update([
                    'ic_img' => $fileUrl, // Store Google Drive URL
                ]);
            } catch (\Exception $e) {
                // If update fails, try direct DB update
                try {
                    DB::table('ownercar')
                        ->where('ownerID', $vehicle->owner->ownerID)
                        ->update(['ic_img' => $fileUrl]);
                } catch (\Exception $e2) {
                    Log::error('Failed to update ownercar ic_img: ' . $e2->getMessage());
                    throw $e2;
                }
            }

            return redirect()->route('admin.vehicles.show', ['vehicle' => $vehicle->vehicleID, 'tab' => 'owner-info'])->with('success', 'Owner IC uploaded successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to upload IC: ' . $e->getMessage());
        }
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

