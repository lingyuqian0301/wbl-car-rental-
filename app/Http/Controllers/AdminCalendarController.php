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
        $bookingsQuery = Booking::with(['customer.user', 'payments'])
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

        // Group bookings by date for calendar display - separate pickups and returns
        $bookingsByDate = [];
        $pickupsByDate = [];
        $returnsByDate = [];
        
        foreach ($bookings as $booking) {
            // Get pickup date (rental_start_date)
            $pickupDate = $booking->rental_start_date;
            if (!$pickupDate instanceof \Carbon\Carbon) {
                $pickupDate = Carbon::parse($pickupDate);
            }
            $pickupDateKey = $pickupDate->format('Y-m-d');
            
            // Get return date (rental_end_date)
            $returnDate = $booking->rental_end_date;
            if (!$returnDate instanceof \Carbon\Carbon) {
                $returnDate = Carbon::parse($returnDate);
            }
            $returnDateKey = $returnDate->format('Y-m-d');
            
            // Count pickups
            if (!isset($pickupsByDate[$pickupDateKey])) {
                $pickupsByDate[$pickupDateKey] = [];
            }
            $pickupsByDate[$pickupDateKey][] = $booking;
            
            // Count returns
            if (!isset($returnsByDate[$returnDateKey])) {
                $returnsByDate[$returnDateKey] = [];
            }
            $returnsByDate[$returnDateKey][] = $booking;
            
            // Also keep all bookings for the date range (for popup display)
            $start = $pickupDate;
            $end = $returnDate;
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
            'pickupsByDate' => $pickupsByDate,
            'returnsByDate' => $returnsByDate,
            'cars' => $cars,
            'motorcycles' => $motorcycles,
            'selectedVehicle' => $vehicleId,
            'currentView' => $view,
            'currentDate' => $currentDate,
        ]);
    }
}



