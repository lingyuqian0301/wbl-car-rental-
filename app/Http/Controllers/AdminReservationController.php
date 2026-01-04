<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Car;
use App\Models\Motorcycle;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;

class AdminReservationController extends Controller
{
    public function index(Request $request): View
    {
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $vehicleId = $request->get('vehicle_id', 'all');
        $sortBy = $request->get('sort_by', 'latest');

        $query = Booking::with(['user', 'payments']);

        // Filter by date range
        if ($dateFrom) {
            $query->where('rental_start_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->where('rental_end_date', '<=', $dateTo);
        }

        // Filter by vehicle
        if ($vehicleId !== 'all') {
            if (str_starts_with($vehicleId, 'car_')) {
                $carId = str_replace('car_', '', $vehicleId);
                $query->where('vehicleID', $carId);
            } elseif (str_starts_with($vehicleId, 'motorcycle_')) {
                $motorcycleId = str_replace('motorcycle_', '', $vehicleId);
                $query->where('vehicleID', $motorcycleId);
            } else {
                $query->where('vehicleID', $vehicleId);
            }
        }

        // Sort by
        switch ($sortBy) {
            case 'latest':
                $query->orderBy('creationDate', 'desc');
                break;
            case 'oldest':
                $query->orderBy('creationDate', 'asc');
                break;
            case 'start_date_asc':
                $query->orderBy('rental_start_date', 'asc');
                break;
            case 'start_date_desc':
                $query->orderBy('rental_start_date', 'desc');
                break;
            default:
                $query->orderBy('creationDate', 'desc');
        }

        $bookings = $query->paginate(20)->withQueryString();

        // Get all vehicles for filter - join with vehicle table
        $cars = Car::with('vehicle')
            ->join('vehicle', 'car.vehicleID', '=', 'vehicle.vehicleID')
            ->select('car.*', 'vehicle.vehicle_brand', 'vehicle.vehicle_model', 'vehicle.plate_number')
            ->orderBy('vehicle.vehicle_brand')
            ->orderBy('vehicle.vehicle_model')
            ->get();
        $motorcycles = Motorcycle::with('vehicle')
            ->join('vehicle', 'motorcycle.vehicleID', '=', 'vehicle.vehicleID')
            ->select('motorcycle.*', 'vehicle.vehicle_brand', 'vehicle.vehicle_model', 'vehicle.plate_number')
            ->orderBy('vehicle.vehicle_brand')
            ->orderBy('vehicle.vehicle_model')
            ->get();

        // Summary stats for header
        $today = Carbon::today();
        $totalBookings = Booking::count();
        $totalPending = Booking::where('booking_status', 'Pending')->count();
        $totalConfirmed = Booking::where('booking_status', 'Confirmed')->count();
        $totalToday = Booking::whereDate('rental_start_date', $today)->count();

        return view('admin.reservations.index', [
            'bookings' => $bookings,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'selectedVehicle' => $vehicleId,
            'sortBy' => $sortBy,
            'cars' => $cars,
            'motorcycles' => $motorcycles,
            'totalBookings' => $totalBookings,
            'totalPending' => $totalPending,
            'totalConfirmed' => $totalConfirmed,
            'totalToday' => $totalToday,
            'today' => $today,
        ]);
    }

    public function updatePaymentStatus(Request $request, Booking $booking)
    {
        $request->validate([
            'payment_status' => 'required|in:Unpaid,Deposit Only,Fully Paid',
        ]);

        // This is a display status, not stored in database
        // The actual payment status is calculated from payments
        // We could store this as a note or flag if needed
        
        return response()->json([
            'success' => true,
            'message' => 'Payment status display updated.',
        ]);
    }
}
