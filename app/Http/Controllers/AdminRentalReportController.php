<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Car;
use App\Models\Motorcycle;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminRentalReportController extends Controller
{
    public function index(Request $request): View
    {
        // Get filter parameters
        $dateRange = $request->get('date_range', 'all'); // 'daily', 'monthly', 'yearly', 'all'
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $vehicleType = $request->get('vehicle_type', 'all'); // 'all', 'car', 'motorcycle'
        $bookingStatus = $request->get('booking_status', 'all'); // 'all', 'done', 'coming', 'cancelled'
        $paymentStatus = $request->get('payment_status', 'all'); // 'all', 'deposit', 'full'
        $customerId = $request->get('customer_id');
        $customerName = $request->get('customer_name');
        $vehicleId = $request->get('vehicle_id');
        $vehicleBrand = $request->get('vehicle_brand');
        $vehicleModel = $request->get('vehicle_model');
        $plateNo = $request->get('plate_no');

        $query = Booking::with(['user', 'payments']);

        // Date range filter
        if ($dateRange === 'daily') {
            $query->whereDate('rental_start_date', Carbon::today());
        } elseif ($dateRange === 'monthly') {
            $query->whereMonth('rental_start_date', Carbon::now()->month)
                  ->whereYear('rental_start_date', Carbon::now()->year);
        } elseif ($dateRange === 'yearly') {
            $query->whereYear('rental_start_date', Carbon::now()->year);
        } elseif ($dateFrom && $dateTo) {
            $query->whereBetween('rental_start_date', [$dateFrom, $dateTo]);
        }

        // Vehicle type filter
        if ($vehicleType !== 'all') {
            // This will be handled by checking vehicle type in the view
            // We'll filter after getting results
        }

        // Booking status filter
        if ($bookingStatus === 'done') {
            $query->where('booking_status', 'Completed');
        } elseif ($bookingStatus === 'coming') {
            $query->whereIn('booking_status', ['Pending', 'Confirmed'])
                  ->where('rental_start_date', '>=', Carbon::today());
        } elseif ($bookingStatus === 'cancelled') {
            $query->where('booking_status', 'Cancelled');
        }

        // Customer filter
        if ($customerId) {
            $query->where('user_id', $customerId);
        }
        if ($customerName) {
            $query->whereHas('user', function($q) use ($customerName) {
                $q->where('name', 'like', "%{$customerName}%");
            });
        }

        // Vehicle filter
        if ($vehicleId) {
            $query->where('vehicleID', $vehicleId);
        }
        if ($vehicleBrand) {
            // Will filter in view
        }
        if ($vehicleModel) {
            // Will filter in view
        }
        if ($plateNo) {
            // Will filter in view
        }

        $bookings = $query->orderBy('rental_start_date', 'desc')->get();

        // Filter by vehicle type, payment status, and vehicle details in PHP
        $filteredBookings = $bookings->filter(function($booking) use ($vehicleType, $paymentStatus, $vehicleBrand, $vehicleModel, $plateNo) {
            $vehicle = $booking->vehicle;
            
            // Vehicle type filter
            if ($vehicleType !== 'all') {
                if ($vehicleType === 'car' && !($vehicle instanceof Car)) {
                    return false;
                }
                if ($vehicleType === 'motorcycle' && !($vehicle instanceof Motorcycle)) {
                    return false;
                }
            }

            // Vehicle details filter
            if ($vehicleBrand && (!$vehicle || stripos($vehicle->vehicle_brand ?? '', $vehicleBrand) === false)) {
                return false;
            }
            if ($vehicleModel && (!$vehicle || stripos($vehicle->vehicle_model ?? '', $vehicleModel) === false)) {
                return false;
            }
            if ($plateNo && (!$vehicle || stripos($vehicle->plate_number ?? $vehicle->plate_no ?? '', $plateNo) === false)) {
                return false;
            }

            // Payment status filter
            if ($paymentStatus !== 'all') {
                $totalPaid = $booking->payments()->where('payment_status', 'Verified')->sum('amount');
                if ($paymentStatus === 'deposit' && ($totalPaid >= $booking->total_price || $totalPaid == 0)) {
                    return false;
                }
                if ($paymentStatus === 'full' && $totalPaid < $booking->total_price) {
                    return false;
                }
            }

            return true;
        });

        // Calculate summaries
        $summaries = $this->calculateSummaries($filteredBookings, $vehicleType);

        // Get filter options - join with vehicle table for cars and motorcycles
        $customers = User::where('role', 'customer')->orderBy('name')->get();
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
        $totalBookings = Booking::count();
        $today = Carbon::today();
        $bookingsToday = Booking::whereDate('rental_start_date', $today)->count();
        $completedBookings = Booking::where('booking_status', 'Completed')->count();
        $totalRevenue = Payment::where('payment_status', 'Verified')->sum('total_amount');

        return view('admin.reports.rentals', [
            'bookings' => $filteredBookings,
            'summaries' => $summaries,
            'dateRange' => $dateRange,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'vehicleType' => $vehicleType,
            'bookingStatus' => $bookingStatus,
            'paymentStatus' => $paymentStatus,
            'customerId' => $customerId,
            'customerName' => $customerName,
            'vehicleId' => $vehicleId,
            'vehicleBrand' => $vehicleBrand,
            'vehicleModel' => $vehicleModel,
            'plateNo' => $plateNo,
            'customers' => $customers,
            'cars' => $cars,
            'motorcycles' => $motorcycles,
            'totalBookings' => $totalBookings,
            'bookingsToday' => $bookingsToday,
            'completedBookings' => $completedBookings,
            'totalRevenue' => $totalRevenue,
            'today' => $today,
        ]);
    }

    public function exportPDF(Request $request): Response
    {
        // Get same filters as index
        $dateRange = $request->get('date_range', 'all');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $vehicleType = $request->get('vehicle_type', 'all');
        $bookingStatus = $request->get('booking_status', 'all');
        $paymentStatus = $request->get('payment_status', 'all');
        $customerId = $request->get('customer_id');
        $customerName = $request->get('customer_name');
        $vehicleId = $request->get('vehicle_id');
        $vehicleBrand = $request->get('vehicle_brand');
        $vehicleModel = $request->get('vehicle_model');
        $plateNo = $request->get('plate_no');

        $query = Booking::with(['user', 'payments']);

        // Apply same filters as index
        if ($dateRange === 'daily') {
            $query->whereDate('rental_start_date', Carbon::today());
        } elseif ($dateRange === 'monthly') {
            $query->whereMonth('rental_start_date', Carbon::now()->month)
                  ->whereYear('rental_start_date', Carbon::now()->year);
        } elseif ($dateRange === 'yearly') {
            $query->whereYear('rental_start_date', Carbon::now()->year);
        } elseif ($dateFrom && $dateTo) {
            $query->whereBetween('rental_start_date', [$dateFrom, $dateTo]);
        }

        if ($bookingStatus === 'done') {
            $query->where('booking_status', 'Completed');
        } elseif ($bookingStatus === 'coming') {
            $query->whereIn('booking_status', ['Pending', 'Confirmed'])
                  ->where('rental_start_date', '>=', Carbon::today());
        } elseif ($bookingStatus === 'cancelled') {
            $query->where('booking_status', 'Cancelled');
        }

        if ($customerId) {
            $query->where('user_id', $customerId);
        }
        if ($customerName) {
            $query->whereHas('user', function($q) use ($customerName) {
                $q->where('name', 'like', "%{$customerName}%");
            });
        }
        if ($vehicleId) {
            $query->where('vehicleID', $vehicleId);
        }

        $bookings = $query->orderBy('rental_start_date', 'desc')->get();

        // Apply same filters
        $filteredBookings = $bookings->filter(function($booking) use ($vehicleType, $paymentStatus, $vehicleBrand, $vehicleModel, $plateNo) {
            $vehicle = $booking->vehicle;
            
            if ($vehicleType !== 'all') {
                if ($vehicleType === 'car' && !($vehicle instanceof Car)) return false;
                if ($vehicleType === 'motorcycle' && !($vehicle instanceof Motorcycle)) return false;
            }

            if ($vehicleBrand && (!$vehicle || stripos($vehicle->vehicle_brand ?? '', $vehicleBrand) === false)) return false;
            if ($vehicleModel && (!$vehicle || stripos($vehicle->vehicle_model ?? '', $vehicleModel) === false)) return false;
            if ($plateNo && (!$vehicle || stripos($vehicle->plate_number ?? $vehicle->plate_no ?? '', $plateNo) === false)) return false;

            if ($paymentStatus !== 'all') {
                $totalPaid = $booking->payments()->where('payment_status', 'Verified')->sum('amount');
                if ($paymentStatus === 'deposit' && ($totalPaid >= $booking->total_price || $totalPaid == 0)) return false;
                if ($paymentStatus === 'full' && $totalPaid < $booking->total_price) return false;
            }

            return true;
        });

        $summaries = $this->calculateSummaries($filteredBookings, $vehicleType);

        $pdf = Pdf::loadView('admin.reports.rentals-pdf', [
            'bookings' => $filteredBookings,
            'summaries' => $summaries,
            'filters' => $request->all(),
        ]);

        return $pdf->download('rental-report-' . date('Y-m-d') . '.pdf');
    }

    private function calculateSummaries($bookings, $vehicleType)
    {
        $totalBookings = $bookings->count();
        $totalRevenue = $bookings->sum(function($booking) {
            return $booking->payments()->where('payment_status', 'Verified')->sum('amount');
        });
        $cancelledBookings = $bookings->where('booking_status', 'Cancelled')->count();

        // Most frequently booked vehicle (overall)
        $vehicleCounts = [];
        foreach ($bookings as $booking) {
            $vehicle = $booking->vehicle;
            if ($vehicle) {
                $key = ($vehicle->vehicle_brand ?? '') . ' ' . ($vehicle->vehicle_model ?? '') . ' (' . ($vehicle->plate_number ?? $vehicle->plate_no ?? '') . ')';
                $vehicleCounts[$key] = ($vehicleCounts[$key] ?? 0) + 1;
            }
        }
        arsort($vehicleCounts);
        $mostFrequentVehicle = key($vehicleCounts) ?? 'N/A';

        // Most frequently booked car
        $carCounts = [];
        foreach ($bookings as $booking) {
            $vehicle = $booking->vehicle;
            if ($vehicle instanceof Car) {
                $key = ($vehicle->vehicle_brand ?? '') . ' ' . ($vehicle->vehicle_model ?? '');
                $carCounts[$key] = ($carCounts[$key] ?? 0) + 1;
            }
        }
        arsort($carCounts);
        $mostFrequentCar = key($carCounts) ?? 'N/A';

        // Most frequently booked motorcycle
        $motorcycleCounts = [];
        foreach ($bookings as $booking) {
            $vehicle = $booking->vehicle;
            if ($vehicle instanceof Motorcycle) {
                $key = ($vehicle->vehicle_brand ?? '') . ' ' . ($vehicle->vehicle_model ?? '');
                $motorcycleCounts[$key] = ($motorcycleCounts[$key] ?? 0) + 1;
            }
        }
        arsort($motorcycleCounts);
        $mostFrequentMotorcycle = key($motorcycleCounts) ?? 'N/A';

        // Peak booked period (month with most bookings)
        $monthCounts = [];
        foreach ($bookings as $booking) {
            if ($booking->rental_start_date) {
                $month = Carbon::parse($booking->rental_start_date)->format('Y-m');
                $monthCounts[$month] = ($monthCounts[$month] ?? 0) + 1;
            }
        }
        arsort($monthCounts);
        $peakPeriod = key($monthCounts) ? Carbon::parse(key($monthCounts) . '-01')->format('F Y') : 'N/A';

        // Most active faculty
        $facultyCounts = [];
        foreach ($bookings as $booking) {
            $faculty = $booking->user->faculty ?? null;
            if ($faculty) {
                $facultyCounts[$faculty] = ($facultyCounts[$faculty] ?? 0) + 1;
            }
        }
        arsort($facultyCounts);
        $mostActiveFaculty = key($facultyCounts) ?? 'N/A';
        $facultyBookingCount = $facultyCounts[$mostActiveFaculty] ?? 0;

        return [
            'totalBookings' => $totalBookings,
            'totalRevenue' => $totalRevenue,
            'cancelledBookings' => $cancelledBookings,
            'mostFrequentVehicle' => $mostFrequentVehicle,
            'mostFrequentCar' => $mostFrequentCar,
            'mostFrequentMotorcycle' => $mostFrequentMotorcycle,
            'peakPeriod' => $peakPeriod,
            'mostActiveFaculty' => $mostActiveFaculty,
            'facultyBookingCount' => $facultyBookingCount,
        ];
    }
}






