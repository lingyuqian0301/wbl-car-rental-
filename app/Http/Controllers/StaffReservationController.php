<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffReservationController extends Controller
{
    public function index(Request $request): View
    {
        // Reuse the same logic as AdminReservationController but return staff view
        $query = Booking::with(['user', 'payments']);

        // Date range filter
        if ($request->filled('date_from')) {
            $query->where('rental_start_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('rental_start_date', '<=', $request->date_to);
        }

        // Vehicle filter
        if ($request->filled('vehicle_id') && $request->vehicle_id !== 'all') {
            $query->where('vehicleID', $request->vehicle_id);
        }

        // Sort by
        $sortBy = $request->get('sort_by', 'latest');
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

        // Get vehicles for filter
        $cars = \App\Models\Car::orderBy('vehicle_brand')->orderBy('vehicle_model')->get();
        $motorcycles = \App\Models\Motorcycle::orderBy('vehicle_brand')->orderBy('vehicle_model')->get();

        return view('staff.reservations.index', [
            'bookings' => $bookings,
            'cars' => $cars,
            'motorcycles' => $motorcycles,
            'sortBy' => $sortBy,
        ]);
    }
}








