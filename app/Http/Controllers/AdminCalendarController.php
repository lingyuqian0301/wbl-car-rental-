<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Car;
use App\Models\Motorcycle;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
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

    /**
     * Get all vehicles with availability status for a specific date
     */
    public function getVehiclesForDate(Request $request): JsonResponse
    {
        $date = $request->get('date');
        if (!$date) {
            return response()->json(['error' => 'Date is required'], 400);
        }

        try {
            $targetDate = Carbon::parse($date)->startOfDay();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid date format'], 400);
        }

        // Get all vehicles (cars and motorcycles)
        $vehicles = Vehicle::where('isActive', true)
            ->with(['car', 'motorcycle'])
            ->orderBy('plate_number')
            ->get();

        // Get bookings for this date
        $bookingsOnDate = Booking::where('booking_status', '!=', 'Cancelled')
            ->whereDate('rental_start_date', '<=', $targetDate)
            ->whereDate('rental_end_date', '>=', $targetDate)
            ->pluck('vehicleID')
            ->toArray();

        $vehiclesData = $vehicles->map(function($vehicle) use ($bookingsOnDate, $targetDate) {
            $isBooked = in_array($vehicle->vehicleID, $bookingsOnDate);
            $isMaintenance = $vehicle->availability_status === 'maintenance';
            $isAvailable = !$isBooked && !$isMaintenance && $vehicle->isActive;

            // Get vehicle type
            $vehicleType = $vehicle->car ? 'Car' : ($vehicle->motorcycle ? 'Motorcycle' : 'Unknown');
            $fullModel = $vehicle->vehicle_brand . ' ' . $vehicle->vehicle_model;

            return [
                'vehicleID' => $vehicle->vehicleID,
                'plate_number' => $vehicle->plate_number,
                'full_model' => $fullModel,
                'vehicle_type' => $vehicleType,
                'is_available' => $isAvailable,
                'is_booked' => $isBooked,
                'is_maintenance' => $isMaintenance,
                'availability_status' => $vehicle->availability_status,
            ];
        });

        return response()->json([
            'date' => $targetDate->format('Y-m-d'),
            'vehicles' => $vehiclesData,
        ]);
    }

    /**
     * Update vehicle availability status
     */
    public function updateVehicleAvailability(Request $request): JsonResponse
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicle,vehicleID',
            'availability_status' => 'required|in:available,rented,maintenance',
        ]);

        try {
            $vehicle = Vehicle::findOrFail($request->vehicle_id);
            $vehicle->availability_status = $request->availability_status;
            $vehicle->save();

            return response()->json([
                'success' => true,
                'message' => 'Vehicle availability updated successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update vehicle availability: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get vehicle availability status summary for today
     */
    public function getVehicleStatusSummary(Request $request): JsonResponse
    {
        try {
            $date = $request->get('date', Carbon::today()->format('Y-m-d'));
            $targetDate = Carbon::parse($date)->startOfDay();
            
            // Get all active vehicles
            $allVehicles = Vehicle::where('isActive', true)
                ->with(['car', 'motorcycle'])
                ->orderBy('plate_number')
                ->get();
            
            // Get bookings for this date
            $bookingsOnDate = Booking::where('booking_status', '!=', 'Cancelled')
                ->whereDate('rental_start_date', '<=', $targetDate)
                ->whereDate('rental_end_date', '>=', $targetDate)
                ->pluck('vehicleID')
                ->toArray();
            
            $availableCount = 0;
            $bookedCount = 0;
            $maintenanceCount = 0;
            $vehiclesList = [];
            
            foreach ($allVehicles as $vehicle) {
                $isBooked = in_array($vehicle->vehicleID, $bookingsOnDate);
                $isMaintenance = $vehicle->availability_status === 'maintenance';
                
                // Determine status
                $status = 'available';
                $statusText = 'Available';
                $statusClass = 'status-available';
                $statusIcon = 'bi-check-circle-fill';
                
                if ($isMaintenance) {
                    $maintenanceCount++;
                    $status = 'maintenance';
                    $statusText = 'Maintenance';
                    $statusClass = 'status-maintenance';
                    $statusIcon = 'bi-tools';
                } elseif ($isBooked) {
                    $bookedCount++;
                    $status = 'booked';
                    $statusText = 'Booked';
                    $statusClass = 'status-booked';
                    $statusIcon = 'bi-x-circle-fill';
                } else {
                    $availableCount++;
                }
                
                // Get vehicle type
                $vehicleType = $vehicle->car ? 'Car' : ($vehicle->motorcycle ? 'Motorcycle' : 'Unknown');
                
                $vehiclesList[] = [
                    'vehicleID' => $vehicle->vehicleID,
                    'plate_number' => $vehicle->plate_number ?? 'N/A',
                    'vehicle_type' => $vehicleType,
                    'status' => $status,
                    'statusText' => $statusText,
                    'statusClass' => $statusClass,
                    'statusIcon' => $statusIcon,
                ];
            }
            
            $totalCount = $allVehicles->count();
            
            return response()->json([
                'success' => true,
                'date' => $targetDate->format('Y-m-d'),
                'total' => $totalCount,
                'available' => $availableCount,
                'booked' => $bookedCount,
                'maintenance' => $maintenanceCount,
                'vehicles' => $vehiclesList,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get vehicle status summary: ' . $e->getMessage(),
            ], 500);
        }
    }
}



