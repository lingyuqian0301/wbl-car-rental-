<?php

namespace App\Http\Controllers;

use App\Models\OwnerCar;
use App\Models\Car;
use App\Models\Booking;
use App\Models\CarImg;
use App\Models\GrantDoc;
use App\Models\Roadtax;
use App\Models\Insurance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;

class AdminLeasingController extends Controller
{
    /**
     * Owner Leasing Index - List all owners with their cars
     */
    public function ownerIndex(Request $request): View
    {
        $query = OwnerCar::with([]);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ic_no', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('contact_number', 'like', "%{$search}%");
            });
        }

        $owners = $query->orderBy('registration_date', 'desc')->paginate(20)->withQueryString();

        // Get cars for each owner (we'll need to join or get separately)
        // For now, we'll get all cars and match by some logic
        $allCars = DB::table('car')->get();

        // Summary stats for header
        $today = Carbon::today();
        $totalOwners = OwnerCar::count();
        $activeOwners = OwnerCar::where('isActive', true)->count();
        $totalCars = DB::table('car')->count();

        return view('admin.leasing.owner.index', [
            'owners' => $owners,
            'allCars' => $allCars,
            'totalOwners' => $totalOwners,
            'activeOwners' => $activeOwners,
            'totalCars' => $totalCars,
            'today' => $today,
        ]);
    }

    /**
     * Show Owner Leasing Details
     */
    public function ownerShow(OwnerCar $owner): View
    {
        // Get cars associated with this owner (you may need to adjust this based on your schema)
        $cars = DB::table('car')->get(); // Placeholder - adjust based on your schema
        
        // Get car images (up to 10)
        $carImages = CarImg::limit(10)->get();
        
        // Get documents
        $grantDocs = GrantDoc::all();
        $roadtaxes = Roadtax::all();
        $insurances = Insurance::all();

        return view('admin.leasing.owner.show', [
            'owner' => $owner,
            'cars' => $cars,
            'carImages' => $carImages,
            'grantDocs' => $grantDocs,
            'roadtaxes' => $roadtaxes,
            'insurances' => $insurances,
        ]);
    }

    /**
     * Show create form for owner leasing
     */
    public function ownerCreate(): View
    {
        return view('admin.leasing.owner.create');
    }

    /**
     * Store new owner leasing
     */
    public function ownerStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ic_no' => 'required|string|max:20',
            'contact_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'bankname' => 'nullable|string|max:50',
            'bank_acc_number' => 'nullable|string|max:30',
            'registration_date' => 'nullable|date',
        ]);

        $validated['registration_date'] = $validated['registration_date'] ?? now();

        OwnerCar::create($validated);

        return redirect()->route('admin.leasing.owner')
            ->with('success', 'Owner leasing created successfully.');
    }

    /**
     * Show edit form
     */
    public function ownerEdit(OwnerCar $owner): View
    {
        return view('admin.leasing.owner.edit', ['owner' => $owner]);
    }

    /**
     * Update owner leasing
     */
    public function ownerUpdate(Request $request, OwnerCar $owner): RedirectResponse
    {
        $validated = $request->validate([
            'ic_no' => 'required|string|max:20',
            'contact_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'bankname' => 'nullable|string|max:50',
            'bank_acc_number' => 'nullable|string|max:30',
            'registration_date' => 'nullable|date',
        ]);

        $owner->update($validated);

        return redirect()->route('admin.leasing.owner')
            ->with('success', 'Owner leasing updated successfully.');
    }

    /**
     * Delete owner leasing
     */
    public function ownerDestroy(OwnerCar $owner): RedirectResponse
    {
        $owner->delete();

        return redirect()->route('admin.leasing.owner')
            ->with('success', 'Owner leasing deleted successfully.');
    }

    /**
     * Vehicle Leasing Index - List bookings more than 15 days
     */
    public function vehicleIndex(Request $request): View
    {
        $query = Booking::with(['customer.user', 'vehicle'])
            ->whereRaw('DATEDIFF(rental_end_date, rental_start_date) > 15');

        // Filter by status
        $statusFilter = $request->get('status', 'all');
        $today = Carbon::today();
        
        if ($statusFilter === 'past') {
            $query->where('rental_end_date', '<', $today);
        } elseif ($statusFilter === 'ongoing') {
            $query->where('rental_start_date', '<=', $today)
                  ->where('rental_end_date', '>=', $today);
        } elseif ($statusFilter === 'future') {
            $query->where('rental_start_date', '>', $today);
        }

        // Filter by vehicle type
        $vehicleType = $request->get('vehicle_type', 'all');
        if ($vehicleType !== 'all') {
            // This would need to join with car/motorcycle tables
            // For now, we'll filter after getting results
        }

        $bookings = $query->orderBy('rental_start_date', 'desc')->paginate(20)->withQueryString();

        // Filter by vehicle type in memory if needed
        if ($vehicleType !== 'all') {
            $bookings->getCollection()->transform(function($booking) use ($vehicleType) {
                $vehicle = $booking->vehicle;
                if ($vehicleType === 'car' && !($vehicle instanceof Car)) {
                    return null;
                }
                if ($vehicleType === 'motorcycle' && !($vehicle instanceof \App\Models\Motorcycle)) {
                    return null;
                }
                return $booking;
            })->filter();
        }

        return view('admin.leasing.vehicle.index', [
            'bookings' => $bookings,
            'statusFilter' => $statusFilter,
            'vehicleType' => $vehicleType,
        ]);
    }
}
