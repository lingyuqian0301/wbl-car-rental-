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
        $dateRange = $request->get('date_range', 'all'); // 'daily', 'weekly', 'monthly', 'custom', 'all'
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $vehicleType = $request->get('vehicle_type', 'all'); // 'all', 'car', 'motor', 'motorcycle'
        $bookingStatus = $request->get('booking_status', 'all'); // 'all', 'done', 'upcoming', 'cancelled'
        $paymentStatus = $request->get('payment_status', 'all'); // 'all', 'deposit', 'fully', 'refunded'
        $customerId = $request->get('customer_id');
        $customerName = $request->get('customer_name');
        $vehicleId = $request->get('vehicle_id');
        $vehicleBrand = $request->get('vehicle_brand');
        $vehicleModel = $request->get('vehicle_model');
        $plateNo = $request->get('plate_no');

        $query = Booking::with(['customer.user', 'vehicle', 'payments']);

        // Date range filter
        if ($dateRange === 'daily') {
            $query->whereDate('rental_start_date', Carbon::today());
        } elseif ($dateRange === 'weekly') {
            $startOfWeek = Carbon::now()->startOfWeek();
            $endOfWeek = Carbon::now()->endOfWeek();
            $query->whereBetween('rental_start_date', [$startOfWeek, $endOfWeek]);
        } elseif ($dateRange === 'monthly') {
            $query->whereMonth('rental_start_date', Carbon::now()->month)
                  ->whereYear('rental_start_date', Carbon::now()->year);
        } elseif ($dateRange === 'custom' && $dateFrom && $dateTo) {
            $query->whereBetween('rental_start_date', [$dateFrom, $dateTo]);
        } elseif ($dateFrom && $dateTo) {
            // Also allow custom range even without 'custom' selected
            $query->whereBetween('rental_start_date', [$dateFrom, $dateTo]);
        }

        // Vehicle type filter - handled in PHP filter below
        // Support both 'motor' and 'motorcycle' for compatibility
        $vehicleTypeFilter = $vehicleType;
        if ($vehicleType === 'motor') {
            $vehicleTypeFilter = 'motorcycle';
        }

        // Booking status filter
        if ($bookingStatus === 'done') {
            $query->where(function($q) {
                $q->where('booking_status', 'Done')
                  ->orWhere('booking_status', 'Completed');
            });
        } elseif ($bookingStatus === 'upcoming') {
            $query->whereIn('booking_status', ['Pending', 'Confirmed'])
                  ->where('rental_start_date', '>=', Carbon::today());
        } elseif ($bookingStatus === 'cancelled') {
            $query->where('booking_status', 'Cancelled');
        }

        // Customer filter
        if ($customerId) {
            $query->where('customerID', $customerId);
        }
        if ($customerName) {
            $query->whereHas('customer.user', function($q) use ($customerName) {
                $q->where('name', 'like', "%{$customerName}%");
            });
        }

        // Vehicle filter
        if ($vehicleId) {
            $query->where('vehicleID', $vehicleId);
        }
        if ($vehicleBrand) {
            $query->whereHas('vehicle', function($vQuery) use ($vehicleBrand) {
                $vQuery->where('vehicle_brand', 'like', "%{$vehicleBrand}%");
            });
        }
        if ($vehicleModel) {
            $query->whereHas('vehicle', function($vQuery) use ($vehicleModel) {
                $vQuery->where('vehicle_model', 'like', "%{$vehicleModel}%");
            });
        }
        if ($plateNo) {
            $query->whereHas('vehicle', function($vQuery) use ($plateNo) {
                $vQuery->where('plate_number', 'like', "%{$plateNo}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'booking_date_asc');
        if ($sortBy === 'booking_date_asc') {
            $query->orderBy('rental_start_date', 'asc');
        } elseif ($sortBy === 'booking_id_desc') {
            $query->orderBy('bookingID', 'desc');
        } else {
            $query->orderBy('rental_start_date', 'asc');
        }

        $bookings = $query->get();

        // Filter by vehicle type, payment status, and vehicle details in PHP
        $filteredBookings = $bookings->filter(function($booking) use ($vehicleType, $vehicleTypeFilter, $paymentStatus, $vehicleBrand, $vehicleModel, $plateNo) {
            $vehicle = $booking->vehicle;
            
            // Vehicle type filter
            if ($vehicleType !== 'all') {
                if ($vehicleType === 'car' && !($vehicle instanceof Car)) {
                    return false;
                }
                if (($vehicleType === 'motor' || $vehicleTypeFilter === 'motorcycle') && !($vehicle instanceof Motorcycle)) {
                    return false;
                }
            }

            // Vehicle details filter - already filtered in query, but keep as backup check
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
                $totalRequired = ($booking->rental_amount ?? 0) + ($booking->deposit_amount ?? 0);
                $totalPaid = $booking->payments()->whereIn('payment_status', ['Verified', 'Full'])->sum('total_amount');
                $refundedAmount = $booking->payments()->where('payment_status', 'Refunded')->sum('total_amount');
                
                if ($paymentStatus === 'deposit') {
                    // Deposit: has payments but not full
                    if ($totalPaid >= $totalRequired || $totalPaid == 0) {
                        return false;
                    }
                } elseif ($paymentStatus === 'fully') {
                    // Fully paid
                    if ($totalPaid < $totalRequired) {
                        return false;
                    }
                } elseif ($paymentStatus === 'refunded') {
                    // Refunded: has refunded payments
                    if ($refundedAmount <= 0) {
                        return false;
                    }
                }
            }

            return true;
        });

        // Calculate summaries
        $summaries = $this->calculateSummaries($filteredBookings, $vehicleType);

        // Get filter options - join with vehicle table for cars and motorcycles
        // Get customers (users with customer relationship, not using role column)
        $customers = User::whereHas('customer')->orderBy('name')->get();
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
            'sortBy' => $sortBy,
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

        $query = Booking::with(['customer.user', 'vehicle', 'payments']);

        // Apply same filters as index
        if ($dateRange === 'daily') {
            $query->whereDate('rental_start_date', Carbon::today());
        } elseif ($dateRange === 'weekly') {
            $startOfWeek = Carbon::now()->startOfWeek();
            $endOfWeek = Carbon::now()->endOfWeek();
            $query->whereBetween('rental_start_date', [$startOfWeek, $endOfWeek]);
        } elseif ($dateRange === 'monthly') {
            $query->whereMonth('rental_start_date', Carbon::now()->month)
                  ->whereYear('rental_start_date', Carbon::now()->year);
        } elseif ($dateRange === 'custom' && $dateFrom && $dateTo) {
            $query->whereBetween('rental_start_date', [$dateFrom, $dateTo]);
        } elseif ($dateFrom && $dateTo) {
            $query->whereBetween('rental_start_date', [$dateFrom, $dateTo]);
        }

        if ($bookingStatus === 'done') {
            $query->where(function($q) {
                $q->where('booking_status', 'Done')
                  ->orWhere('booking_status', 'Completed');
            });
        } elseif ($bookingStatus === 'upcoming') {
            $query->whereIn('booking_status', ['Pending', 'Confirmed'])
                  ->where('rental_start_date', '>=', Carbon::today());
        } elseif ($bookingStatus === 'cancelled') {
            $query->where('booking_status', 'Cancelled');
        }

        if ($customerId) {
            $query->where('customerID', $customerId);
        }
        if ($customerName) {
            $query->whereHas('customer.user', function($q) use ($customerName) {
                $q->where('name', 'like', "%{$customerName}%");
            });
        }
        if ($vehicleId) {
            $query->where('vehicleID', $vehicleId);
        }
        if ($vehicleBrand) {
            $query->whereHas('vehicle', function($vQuery) use ($vehicleBrand) {
                $vQuery->where('vehicle_brand', 'like', "%{$vehicleBrand}%");
            });
        }
        if ($vehicleModel) {
            $query->whereHas('vehicle', function($vQuery) use ($vehicleModel) {
                $vQuery->where('vehicle_model', 'like', "%{$vehicleModel}%");
            });
        }
        if ($plateNo) {
            $query->whereHas('vehicle', function($vQuery) use ($plateNo) {
                $vQuery->where('plate_number', 'like', "%{$plateNo}%");
            });
        }
        if ($vehicleBrand) {
            $query->whereHas('vehicle', function($vQuery) use ($vehicleBrand) {
                $vQuery->where('vehicle_brand', 'like', "%{$vehicleBrand}%");
            });
        }
        if ($vehicleModel) {
            $query->whereHas('vehicle', function($vQuery) use ($vehicleModel) {
                $vQuery->where('vehicle_model', 'like', "%{$vehicleModel}%");
            });
        }
        if ($plateNo) {
            $query->whereHas('vehicle', function($vQuery) use ($plateNo) {
                $vQuery->where('plate_number', 'like', "%{$plateNo}%");
            });
        }

        $bookings = $query->orderBy('rental_start_date', 'desc')->get();

        // Apply same filters
        $filteredBookings = $bookings->filter(function($booking) use ($vehicleType, $vehicleTypeFilter, $paymentStatus) {
            $vehicle = $booking->vehicle;
            
            if ($vehicleType !== 'all') {
                if ($vehicleType === 'car' && !($vehicle instanceof Car)) return false;
                if (($vehicleType === 'motor' || $vehicleTypeFilter === 'motorcycle') && !($vehicle instanceof Motorcycle)) return false;
            }

            if ($paymentStatus !== 'all') {
                $totalRequired = ($booking->rental_amount ?? 0) + ($booking->deposit_amount ?? 0);
                $totalPaid = $booking->payments()->whereIn('payment_status', ['Verified', 'Full'])->sum('total_amount');
                $refundedAmount = $booking->payments()->where('payment_status', 'Refunded')->sum('total_amount');
                
                if ($paymentStatus === 'deposit') {
                    if ($totalPaid >= $totalRequired || $totalPaid == 0) return false;
                } elseif ($paymentStatus === 'fully') {
                    if ($totalPaid < $totalRequired) return false;
                } elseif ($paymentStatus === 'refunded') {
                    if ($refundedAmount <= 0) return false;
                }
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






