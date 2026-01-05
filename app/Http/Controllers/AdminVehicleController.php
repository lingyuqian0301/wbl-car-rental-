<?php

namespace App\Http\Controllers;

use App\Models\ItemCategory;
use App\Models\Vehicle;
use App\Models\Voucher;
use App\Models\VoucherUsage;
use App\Models\AdminNotification;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AdminVehicleController extends Controller
{
    public function cars(Request $request): View
    {
        $search = $request->get('search');
        $sortBy = $request->get('sort_by', 'vehicle_id_asc');
        $filterBrand = $request->get('filter_brand');
        $filterModel = $request->get('filter_model');
        $filterSeating = $request->get('filter_seating');
        $filterTransmission = $request->get('filter_transmission');
        $filterCarType = $request->get('filter_car_type');
        $filterStatus = $request->get('filter_status');
        
        // Get cars from car table and join with vehicle table
        $query = \App\Models\Car::with('vehicle')
            ->join('vehicle', 'car.vehicleID', '=', 'vehicle.vehicleID')
            ->select('car.*', 'vehicle.vehicle_brand', 'vehicle.vehicle_model', 'vehicle.plate_number', 'vehicle.availability_status', 'vehicle.rental_price', 'vehicle.created_date', 'vehicle.vehicleID');
        
        // Search
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('vehicle.vehicle_brand', 'like', "%{$search}%")
                  ->orWhere('vehicle.vehicle_model', 'like', "%{$search}%")
                  ->orWhere('vehicle.plate_number', 'like', "%{$search}%");
            });
        }
        
        // Filters
        if ($filterBrand) {
            $query->where('vehicle.vehicle_brand', $filterBrand);
        }
        if ($filterModel) {
            $query->where('vehicle.vehicle_model', $filterModel);
        }
        if ($filterSeating) {
            $query->where('car.seating_capacity', $filterSeating);
        }
        if ($filterTransmission) {
            $query->where('car.transmission', $filterTransmission);
        }
        if ($filterCarType) {
            $query->where('car.car_type', $filterCarType);
        }
        if ($filterStatus) {
            $query->where('vehicle.availability_status', $filterStatus);
        }
        
        // Sorting
        switch ($sortBy) {
            case 'vehicle_id_asc':
                $query->orderBy('vehicle.vehicleID', 'ASC');
                break;
            case 'vehicle_id_desc':
                $query->orderBy('vehicle.vehicleID', 'DESC');
                break;
            case 'brand_asc':
                $query->orderBy('vehicle.vehicle_brand', 'ASC');
                break;
            case 'brand_desc':
                $query->orderBy('vehicle.vehicle_brand', 'DESC');
                break;
            case 'model_asc':
                $query->orderBy('vehicle.vehicle_model', 'ASC');
                break;
            case 'model_desc':
                $query->orderBy('vehicle.vehicle_model', 'DESC');
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
            'filterBrand' => $filterBrand,
            'filterModel' => $filterModel,
            'filterSeating' => $filterSeating,
            'filterTransmission' => $filterTransmission,
            'filterCarType' => $filterCarType,
            'filterStatus' => $filterStatus,
            'brands' => $brands,
            'models' => $models,
            'seatings' => $seatings,
            'transmissions' => $transmissions,
            'carTypes' => $carTypes,
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
        $filterBrand = $request->get('filter_brand');
        $filterModel = $request->get('filter_model');
        $filterMotorType = $request->get('filter_motor_type');
        $filterStatus = $request->get('filter_status');
        
        // Get motorcycles from motorcycle table and join with vehicle table
        $query = \App\Models\Motorcycle::with('vehicle')
            ->join('vehicle', 'motorcycle.vehicleID', '=', 'vehicle.vehicleID')
            ->select('motorcycle.*', 'vehicle.vehicle_brand', 'vehicle.vehicle_model', 'vehicle.plate_number', 'vehicle.availability_status', 'vehicle.rental_price', 'vehicle.created_date', 'vehicle.vehicleID');
        
        // Search
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('vehicle.vehicle_brand', 'like', "%{$search}%")
                  ->orWhere('vehicle.vehicle_model', 'like', "%{$search}%")
                  ->orWhere('vehicle.plate_number', 'like', "%{$search}%");
            });
        }
        
        // Filters
        if ($filterBrand) {
            $query->where('vehicle.vehicle_brand', $filterBrand);
        }
        if ($filterModel) {
            $query->where('vehicle.vehicle_model', $filterModel);
        }
        if ($filterMotorType) {
            $query->where('motorcycle.motor_type', $filterMotorType);
        }
        if ($filterStatus) {
            $query->where('vehicle.availability_status', $filterStatus);
        }
        
        // Sorting
        switch ($sortBy) {
            case 'vehicle_id_asc':
                $query->orderBy('vehicle.vehicleID', 'ASC');
                break;
            case 'vehicle_id_desc':
                $query->orderBy('vehicle.vehicleID', 'DESC');
                break;
            case 'brand_asc':
                $query->orderBy('vehicle.vehicle_brand', 'ASC');
                break;
            case 'brand_desc':
                $query->orderBy('vehicle.vehicle_brand', 'DESC');
                break;
            case 'model_asc':
                $query->orderBy('vehicle.vehicle_model', 'ASC');
                break;
            case 'model_desc':
                $query->orderBy('vehicle.vehicle_model', 'DESC');
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
            'filterBrand' => $filterBrand,
            'filterModel' => $filterModel,
            'filterMotorType' => $filterMotorType,
            'filterStatus' => $filterStatus,
            'brands' => $brands,
            'models' => $models,
            'motorTypes' => $motorTypes,
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
            $filterBrand = $request->get('filter_brand');
            $filterModel = $request->get('filter_model');
            $filterType = $request->get('filter_type'); // all, car, motor, other
            $filterIsActive = $request->get('filter_isactive');
            
            $query = Vehicle::query();
            
            // Filters
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
            
            // Default sort: ASC vehicle ID
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
                'filterBrand' => $filterBrand,
                'filterModel' => $filterModel,
                'filterType' => $filterType,
                'filterIsActive' => $filterIsActive,
                'brands' => $brands,
                'models' => $models,
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
        $vehicle->load([
            'bookings.customer.user',
            'car',
            'motorcycle',
            'owner.personDetails',
            'maintenances' => function($query) {
                $query->orderBy('service_date', 'desc');
            },
            'documents'
        ]);

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

        return view('admin.vehicles.show', [
            'vehicle' => $vehicle,
            'bookedDates' => $bookedDates,
        ]);
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
            'next_due_date' => 'nullable|date',
            'service_center' => 'nullable|string|max:100',
        ]);

        $maintenance = \App\Models\VehicleMaintenance::create([
            'vehicleID' => $vehicle->vehicleID,
            'service_date' => $validated['service_date'],
            'service_type' => $validated['service_type'],
            'description' => $validated['description'] ?? null,
            'mileage' => $validated['mileage'] ?? null,
            'cost' => $validated['cost'],
            'next_due_date' => $validated['next_due_date'] ?? null,
            'service_center' => $validated['service_center'] ?? null,
            'staffID' => Auth::user()->userID ?? null,
        ]);

        // Create notification for staff/admin if next_due_date is within 7 days
        if ($validated['next_due_date']) {
            $nextDue = Carbon::parse($validated['next_due_date']);
            $daysUntilDue = Carbon::today()->diffInDays($nextDue, false);
            
            if ($daysUntilDue <= 7 && $daysUntilDue >= 0) {
                $this->createServiceReminderNotification($vehicle, $maintenance, $nextDue);
            }
        }

        return redirect()->back()->with('success', 'Maintenance record added successfully.');
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
        $maintenance->delete();
        return redirect()->back()->with('success', 'Maintenance record deleted successfully.');
    }

    public function storeDocument(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'document_type' => 'required|string|in:insurance,grant,roadtax,contract',
            'file' => 'required|mimes:jpg,jpeg,png,gif,pdf|max:5120', // 5MB max, accepts images and PDFs
        ]);

        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('vehicle_documents', $fileName, 'public');

        // Check if document type column exists, if not use a different approach
        $documentData = [
            'vehicleID' => $vehicle->vehicleID,
            'fileURL' => $filePath,
            'upload_date' => Carbon::today(),
        ];

        // Add document_type if column exists
        if (Schema::hasColumn('VehicleDocument', 'document_type')) {
            $documentData['document_type'] = $validated['document_type'];
        }

        $document = \App\Models\VehicleDocument::create($documentData);

        return redirect()->back()->with('success', 'Document uploaded successfully.');
    }

    public function storePhoto(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'photo' => 'required|image|max:5120', // 5MB max
            'photo_type' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        $file = $request->file('photo');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('vehicle_photos', $fileName, 'public');

        // Store photo using VehicleDocument with document_type = 'photo'
        $documentData = [
            'vehicleID' => $vehicle->vehicleID,
            'fileURL' => $filePath,
            'upload_date' => Carbon::today(),
        ];

        // Add document_type if column exists
        if (Schema::hasColumn('VehicleDocument', 'document_type')) {
            $documentData['document_type'] = 'photo';
        }

        $document = \App\Models\VehicleDocument::create($documentData);

        return redirect()->back()->with('success', 'Photo uploaded successfully.');
    }

    public function destroyDocument(\App\Models\VehicleDocument $document)
    {
        if ($document->fileURL && \Storage::disk('public')->exists($document->fileURL)) {
            \Storage::disk('public')->delete($document->fileURL);
        }
        $document->delete();
        return redirect()->back()->with('success', 'Document deleted successfully.');
    }

    public function createCar(): View
    {
        return view('admin.vehicles.create-car');
    }

    public function createMotorcycle(): View
    {
        return view('admin.vehicles.create-motorcycle');
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
            return redirect()->back()->with('error', 'Cannot delete car with existing bookings.');
        }

        $vehicle->car()->delete();
        $vehicle->delete();

        return redirect()->route('admin.vehicles.cars')->with('success', 'Car deleted successfully.');
    }

    public function destroyMotorcycle(Vehicle $vehicle)
    {
        if ($vehicle->bookings()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete motorcycle with existing bookings.');
        }

        $vehicle->motorcycle()->delete();
        $vehicle->delete();

        return redirect()->route('admin.vehicles.motorcycles')->with('success', 'Motorcycle deleted successfully.');
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
            return redirect()->back()->with('error', 'Cannot delete vehicle with existing bookings.');
        }

        $vehicle->car()->delete();
        $vehicle->motorcycle()->delete();
        $vehicle->delete();

        return redirect()->back()->with('success', 'Vehicle deleted successfully.');
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

            // Update owner if it already exists
            if ($owner->wasRecentlyCreated === false) {
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
            }

            // Update vehicle to link to owner
            $vehicle->update(['ownerID' => $owner->ownerID]);

            DB::commit();

            return redirect()->back()->with('success', 'Owner information updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Failed to update owner: ' . $e->getMessage());
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

