<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Car;
use App\Models\Motorcycle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;
use Carbon\Carbon;

class AdminCalendarController extends Controller
{
    public function index(Request $request): View
    {
        $view = $request->get('view', 'month'); // month, week, day
        $vehicleId = $request->get('vehicle_id', 'all');
        $currentDate = $request->get('date', now()->format('Y-m-d'));

        // Get bookings based on filters
        // Note: 'vehicle' is not a relationship, it's a custom accessor, so we can't eager load it
        $bookingsQuery = Booking::with(['user', 'payments'])
            ->where('booking_status', '!=', 'Cancelled');

        if ($vehicleId !== 'all') {
            // Handle car_ and motorcycle_ prefixes
            if (str_starts_with($vehicleId, 'car_')) {
                $carId = str_replace('car_', '', $vehicleId);
                $bookingsQuery->where('vehicleID', $carId);
            } elseif (str_starts_with($vehicleId, 'motorcycle_')) {
                $motorcycleId = str_replace('motorcycle_', '', $vehicleId);
                $bookingsQuery->where('vehicleID', $motorcycleId);
            } else {
                $bookingsQuery->where('vehicleID', $vehicleId);
            }
        }

        $bookings = $bookingsQuery->get();

        // Group bookings by date for calendar display
        $bookingsByDate = [];
        foreach ($bookings as $booking) {
            $start = $booking->start_date;
            $end = $booking->end_date;
            
            if (!$start instanceof \Carbon\Carbon) {
                $start = Carbon::parse($start);
            }
            if (!$end instanceof \Carbon\Carbon) {
                $end = Carbon::parse($end);
            } 
            $current = $start->copy();
            while ($current->lte($end)) {
                $dateKey = $current->format('Y-m-d');
                if (!isset($bookingsByDate[$dateKey])) {
                    $bookingsByDate[$dateKey] = [];
                }
                $bookingsByDate[$dateKey][] = $booking;
                $current->addDay();
            }
        }

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

        $viewName = str_starts_with(Route::currentRouteName(), 'staff.') ? 'staff.calendar.index' : 'admin.calendar.index';
        return view($viewName, [
            'bookings' => $bookings,
            'bookingsByDate' => $bookingsByDate,
            'cars' => $cars,
            'motorcycles' => $motorcycles,
            'selectedVehicle' => $vehicleId,
            'currentView' => $view,
            'currentDate' => $currentDate,
        ]);
    }
}



