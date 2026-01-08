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
use App\Models\Vehicle;

class AdminLeasingController extends Controller
{
    /**
     * Combined Leasing Index - Shows both owner and vehicle tabs
     */
    public function index(Request $request): View
    {
        $activeTab = $request->get('tab', 'owner');

        if ($activeTab === 'owner') {
            return $this->ownerIndex($request);
        } else {
            return $this->vehicleIndex($request);
        }
    }

    /**
     * Owner Leasing Index - List all owners with their cars
     */
    public function ownerIndex(Request $request): View
    {
        $query = OwnerCar::with(['personDetails', 'vehicles']);

        // Search by owner name, vehicle plate no, contact no
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ic_no', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('contact_number', 'like', "%{$search}%")
                  ->orWhereHas('personDetails', function($pdQuery) use ($search) {
                      $pdQuery->where('fullname', 'like', "%{$search}%");
                  })
                  ->orWhereHas('vehicles', function($vQuery) use ($search) {
                      $vQuery->where('plate_number', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by isActive
        if ($request->filled('filter_isactive')) {
            $query->where('isActive', $request->filter_isactive == '1');
        }

        // Default sort: asc owner id
        $owners = $query->orderBy('ownerID', 'asc')->paginate(20)->withQueryString();

        // Get cars for each owner (we'll need to join or get separately)
        // For now, we'll get all cars and match by some logic
        $allCars = DB::table('car')->get();

        // Summary stats for header
        $today = Carbon::today();
        $totalOwners = OwnerCar::count();
        $activeOwners = OwnerCar::where('isActive', true)->count();
        $totalCars = DB::table('car')->count();

        return view('leasing.owner', [
            'owners' => $owners,
            'allCars' => $allCars,
            'totalOwners' => $totalOwners,
            'activeOwners' => $activeOwners,
            'totalCars' => $totalCars,
            'today' => $today,
            'request' => $request,
        ]);
    }

    /**
     * Show Owner Leasing Details
     */
    public function ownerShow(OwnerCar $owner): View
    {
        // Load owner with person details
        $owner->load('personDetails');
        
        // Get vehicles for this owner (if any)
        $vehicles = $owner->vehicles()->with(['car', 'motorcycle'])->get();
        
        return view('admin.leasing.owner.show', [
            'owner' => $owner,
            'vehicles' => $vehicles,
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
            'fullname' => 'required|string|max:100',
            'ic_no' => 'required|string|max:20',
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
            // Create PersonDetails if it doesn't exist
            \App\Models\PersonDetails::firstOrCreate(
                ['ic_no' => $validated['ic_no']],
                ['fullname' => $validated['fullname']]
            );

            // Create OwnerCar
            $ownerData = [
                'ic_no' => $validated['ic_no'],
                'contact_number' => $validated['contact_number'] ?? null,
                'email' => $validated['email'] ?? null,
                'bankname' => $validated['bankname'] ?? null,
                'bank_acc_number' => $validated['bank_acc_number'] ?? null,
                'registration_date' => $validated['registration_date'] ?? now(),
                'leasing_price' => $validated['leasing_price'] ?? null,
                'leasing_due_date' => $validated['leasing_due_date'] ?? null,
                'isActive' => $validated['isActive'] ?? true,
            ];

            OwnerCar::create($ownerData);

            DB::commit();

            return redirect()->route('admin.leasing.index', ['tab' => 'owner'])
                ->with('success', 'Owner leasing created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create owner: ' . $e->getMessage());
        }
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
            'leasing_price' => 'nullable|numeric|min:0',
            'leasing_due_date' => 'nullable|date',
            'isActive' => 'nullable|boolean',
        ]);

        $owner->update($validated);

        return redirect()->route('admin.leasing.index', ['tab' => 'owner'])
            ->with('success', 'Owner leasing updated successfully.');
    }

    /**
     * Delete owner leasing
     */
    public function ownerDestroy(OwnerCar $owner): RedirectResponse
    {
        $owner->delete();

        return redirect()->route('admin.leasing.index', ['tab' => 'owner'])
            ->with('success', 'Owner leasing deleted successfully.');
    }

    /**
     * Vehicle Leasing Index - List bookings more than 15 days
     */
    public function vehicleIndex(Request $request): View
    {
        // Get bookings with duration > 15 days
        $query = Booking::with(['customer.user', 'vehicle.documents', 'payments', 'invoice'])
            ->where('booking_status', '!=', 'Cancelled')
            ->where(function($q) {
                // Use duration field if available, otherwise calculate
                $q->where('duration', '>', 15)
                  ->orWhereRaw('DATEDIFF(rental_end_date, rental_start_date) > 15');
            });

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

        // Get all bookings for statistics (before pagination)
        $allBookings = clone $query;
        $allBookingsData = $allBookings->get();

        // Calculate summary statistics
        $totalBookings = $allBookingsData->count();
        $totalRevenue = $allBookingsData->sum(function($booking) {
            return ($booking->deposit_amount ?? 0) + ($booking->rental_amount ?? 0);
        });
        $totalPaid = $allBookingsData->sum(function($booking) {
            return $booking->payments()->where('payment_status', 'Verified')->sum('total_amount');
        });
        $ongoingBookings = $allBookingsData->filter(function($booking) use ($today) {
            return $booking->rental_start_date <= $today && $booking->rental_end_date >= $today;
        })->count();

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

        // Get staff users for served by dropdown
        $staffUsers = \App\Models\User::where(function($query) {
            $query->whereHas('staff')->orWhereHas('admin');
        })->orderBy('name')->get();

        // Get booking statuses
        $bookingStatuses = ['Pending', 'Confirmed', 'Request Cancellation', 'Refunding', 'Cancelled', 'Done'];

        return view('leasing.vehicle', [
            'bookings' => $bookings,
            'statusFilter' => $statusFilter,
            'vehicleType' => $vehicleType,
            'today' => $today,
            'totalBookings' => $totalBookings,
            'totalRevenue' => $totalRevenue,
            'totalPaid' => $totalPaid,
            'ongoingBookings' => $ongoingBookings,
            'staffUsers' => $staffUsers,
            'bookingStatuses' => $bookingStatuses,
        ]);
    }
}
