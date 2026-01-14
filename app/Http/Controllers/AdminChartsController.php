<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Car;
use App\Models\Motorcycle;
use App\Models\StudentDetail;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminChartsController extends Controller
{
    public function index(Request $request): View
    {
        $activeTab = $request->get('tab', 'weekly'); // weekly, monthly, faculty, brand, comparison

        // Weekly rental data
        $selectedWeek = $request->get('selected_week', Carbon::now()->format('Y-\WW'));
        $weekParts = explode('-W', $selectedWeek);
        $weekStart = Carbon::now()->setISODate($weekParts[0], $weekParts[1] ?? 1)->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();
        
        $weeklyData = [];
        for ($date = $weekStart->copy(); $date <= $weekEnd; $date->addDay()) {
            $count = Booking::whereDate('rental_start_date', $date->format('Y-m-d'))
                ->where('booking_status', '!=', 'Cancelled')
                ->count();
            $weeklyData[] = [
                'date' => $date->format('d M'),
                'count' => $count,
            ];
        }

        // Monthly rental data
        $selectedMonth = $request->get('selected_month', Carbon::now()->format('Y-m'));
        $monthParts = explode('-', $selectedMonth);
        $monthStart = Carbon::create($monthParts[0], $monthParts[1], 1);
        $daysInMonth = $monthStart->daysInMonth;
        
        $monthlyData = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($monthParts[0], $monthParts[1], $day);
            $count = Booking::whereDate('rental_start_date', $date->format('Y-m-d'))
                ->where('booking_status', '!=', 'Cancelled')
                ->count();
            $monthlyData[] = [
                'date' => $day,
                'count' => $count,
            ];
        }

        // Faculty rental data - count unique customers per faculty
        $facultyMonth = $request->get('faculty_month', Carbon::now()->format('Y-m'));
        $facultyYear = $request->get('faculty_year', Carbon::now()->year);
        $facultyMonthParts = explode('-', $facultyMonth);
        $facultyMonthStart = Carbon::create($facultyMonthParts[0], $facultyMonthParts[1], 1)->startOfMonth();
        $facultyMonthEnd = $facultyMonthStart->copy()->endOfMonth();
        
        $facultyData = [];
        // Get bookings and join with customer -> localStudent/internationalStudent -> studentDetails to get faculty
        $bookings = Booking::whereBetween('rental_start_date', [$facultyMonthStart, $facultyMonthEnd])
            ->where('booking_status', '!=', 'Cancelled')
            ->with(['customer.localStudent.studentDetails', 'customer.internationalStudent.studentDetails'])
            ->get();
        
        // Track unique customers per faculty
        $facultyCustomers = [];
        foreach ($bookings as $booking) {
            if (!$booking->customer) continue;
            
            $faculty = 'Unknown';
            // Check local student first
            if ($booking->customer->localStudent && $booking->customer->localStudent->studentDetails) {
                $faculty = $booking->customer->localStudent->studentDetails->faculty ?? 'Unknown';
            }
            // Check international student if local student not found
            elseif ($booking->customer->internationalStudent && $booking->customer->internationalStudent->studentDetails) {
                $faculty = $booking->customer->internationalStudent->studentDetails->faculty ?? 'Unknown';
            }
            
            if (!isset($facultyCustomers[$faculty])) {
                $facultyCustomers[$faculty] = [];
            }
            // Track unique customer IDs
            $customerID = $booking->customer->customerID;
            if (!in_array($customerID, $facultyCustomers[$faculty])) {
                $facultyCustomers[$faculty][] = $customerID;
            }
        }
        
        // Count unique customers per faculty
        foreach ($facultyCustomers as $faculty => $customerIDs) {
            $facultyData[$faculty] = count($customerIDs);
        }

        // Vehicle rental data - count bookings per vehicle (show ALL vehicles)
        $brandMonth = $request->get('brand_month', Carbon::now()->format('Y-m'));
        $brandVehicleType = $request->get('brand_vehicle_type', 'all');
        $brandMonthParts = explode('-', $brandMonth);
        $brandMonthStart = Carbon::create($brandMonthParts[0], $brandMonthParts[1], 1)->startOfMonth();
        $brandMonthEnd = $brandMonthStart->copy()->endOfMonth();
        
        // Get ALL active vehicles
        $allVehicles = \App\Models\Vehicle::where('isActive', true)
            ->orderBy('plate_number')
            ->get();
        
        // Get bookings for the selected month
        $brandBookings = Booking::whereBetween('rental_start_date', [$brandMonthStart, $brandMonthEnd])
            ->where('booking_status', '!=', 'Cancelled')
            ->with('vehicle')
            ->get();
        
        // Initialize brandData with all vehicles (set to 0)
        $brandData = [];
        foreach ($allVehicles as $vehicle) {
            $plateNumber = $vehicle->plate_number ?? 'N/A';
            $brandData[$plateNumber] = 0;
        }
        
        // Count bookings for each vehicle
        foreach ($brandBookings as $booking) {
            $vehicle = $booking->vehicle;
            if (!$vehicle) continue;
            
            $plateNumber = $vehicle->plate_number ?? 'N/A';
            if (isset($brandData[$plateNumber])) {
                $brandData[$plateNumber]++;
            } else {
                // If vehicle not in active list, add it
                $brandData[$plateNumber] = 1;
            }
        }
        
        // Sort by plate number (alphabetically)
        ksort($brandData);

        // Comparison data (last 4 months)
        $comparisonData = [];
        for ($i = 3; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();
            
            $total = Booking::whereBetween('rental_start_date', [$monthStart, $monthEnd])
                ->where('booking_status', '!=', 'Cancelled')
                ->count();
            
            $cars = Booking::whereBetween('rental_start_date', [$monthStart, $monthEnd])
                ->where('booking_status', '!=', 'Cancelled')
                ->with('vehicle')
                ->get()
                ->filter(function($booking) {
                    return $booking->vehicle instanceof Car;
                })
                ->count();
            
            $motorcycles = Booking::whereBetween('rental_start_date', [$monthStart, $monthEnd])
                ->where('booking_status', '!=', 'Cancelled')
                ->with('vehicle')
                ->get()
                ->filter(function($booking) {
                    return $booking->vehicle instanceof Motorcycle;
                })
                ->count();
            
            $comparisonData[] = [
                'month' => $month->format('M Y'),
                'total' => $total,
                'cars' => $cars,
                'motorcycles' => $motorcycles,
            ];
        }

        return view('admin.charts.index', [
            'activeTab' => $activeTab,
            'weeklyData' => $weeklyData,
            'monthlyData' => $monthlyData,
            'selectedWeek' => $selectedWeek,
            'selectedMonth' => $selectedMonth,
            'facultyMonth' => $facultyMonth,
            'facultyYear' => $facultyYear,
            'facultyData' => $facultyData,
            'brandMonth' => $brandMonth,
            'brandVehicleType' => $brandVehicleType,
            'brandData' => $brandData,
            'comparisonData' => $comparisonData,
        ]);
    }

    public function exportPdf(Request $request): Response
    {
        $activeTab = $request->get('tab', 'weekly');
        $selectedWeek = $request->get('selected_week', Carbon::now()->format('Y-\WW'));
        $selectedMonth = $request->get('selected_month', Carbon::now()->format('Y-m'));
        $facultyMonth = $request->get('faculty_month', Carbon::now()->format('Y-m'));
        $facultyYear = $request->get('faculty_year', Carbon::now()->year);
        $brandMonth = $request->get('brand_month', Carbon::now()->format('Y-m'));
        $brandVehicleType = $request->get('brand_vehicle_type', 'all');

        // Weekly rental data
        $weekParts = explode('-W', $selectedWeek);
        $weekStart = Carbon::now()->setISODate($weekParts[0], $weekParts[1] ?? 1)->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();
        
        $weeklyData = [];
        for ($date = $weekStart->copy(); $date <= $weekEnd; $date->addDay()) {
            $count = Booking::whereDate('rental_start_date', $date->format('Y-m-d'))
                ->where('booking_status', '!=', 'Cancelled')
                ->count();
            $weeklyData[] = [
                'date' => $date->format('d M'),
                'count' => $count,
            ];
        }

        // Monthly rental data
        $monthParts = explode('-', $selectedMonth);
        $monthStart = Carbon::create($monthParts[0], $monthParts[1], 1);
        $daysInMonth = $monthStart->daysInMonth;
        
        $monthlyData = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($monthParts[0], $monthParts[1], $day);
            $count = Booking::whereDate('rental_start_date', $date->format('Y-m-d'))
                ->where('booking_status', '!=', 'Cancelled')
                ->count();
            $monthlyData[] = [
                'date' => $day,
                'count' => $count,
            ];
        }

        // Faculty rental data - count unique customers per faculty
        $facultyMonthParts = explode('-', $facultyMonth);
        $facultyMonthStart = Carbon::create($facultyMonthParts[0], $facultyMonthParts[1], 1)->startOfMonth();
        $facultyMonthEnd = $facultyMonthStart->copy()->endOfMonth();
        
        $facultyData = [];
        $bookings = Booking::whereBetween('rental_start_date', [$facultyMonthStart, $facultyMonthEnd])
            ->where('booking_status', '!=', 'Cancelled')
            ->with(['customer.localStudent.studentDetails', 'customer.internationalStudent.studentDetails'])
            ->get();
        
        // Track unique customers per faculty
        $facultyCustomers = [];
        foreach ($bookings as $booking) {
            if (!$booking->customer) continue;
            
            $faculty = 'Unknown';
            // Check local student first
            if ($booking->customer->localStudent && $booking->customer->localStudent->studentDetails) {
                $faculty = $booking->customer->localStudent->studentDetails->faculty ?? 'Unknown';
            }
            // Check international student if local student not found
            elseif ($booking->customer->internationalStudent && $booking->customer->internationalStudent->studentDetails) {
                $faculty = $booking->customer->internationalStudent->studentDetails->faculty ?? 'Unknown';
            }
            
            if (!isset($facultyCustomers[$faculty])) {
                $facultyCustomers[$faculty] = [];
            }
            // Track unique customer IDs
            $customerID = $booking->customer->customerID;
            if (!in_array($customerID, $facultyCustomers[$faculty])) {
                $facultyCustomers[$faculty][] = $customerID;
            }
        }
        
        // Count unique customers per faculty
        foreach ($facultyCustomers as $faculty => $customerIDs) {
            $facultyData[$faculty] = count($customerIDs);
        }

        // Brand rental data - show ALL vehicles with plate numbers
        $brandMonthParts = explode('-', $brandMonth);
        $brandMonthStart = Carbon::create($brandMonthParts[0], $brandMonthParts[1], 1)->startOfMonth();
        $brandMonthEnd = $brandMonthStart->copy()->endOfMonth();
        
        // Get ALL active vehicles
        $allVehicles = Vehicle::where('isActive', true)
            ->orderBy('plate_number')
            ->get();
        
        // Filter by vehicle type if specified
        if ($brandVehicleType !== 'all') {
            $allVehicles = $allVehicles->filter(function($vehicle) use ($brandVehicleType) {
                if ($brandVehicleType === 'car') {
                    return $vehicle->car !== null;
                } elseif ($brandVehicleType === 'motorcycle') {
                    return $vehicle->motorcycle !== null;
                }
                return true;
            });
        }
        
        // Get bookings for the selected month
        $brandBookings = Booking::whereBetween('rental_start_date', [$brandMonthStart, $brandMonthEnd])
            ->where('booking_status', '!=', 'Cancelled')
            ->with('vehicle')
            ->get();
        
        // Initialize brandData with all vehicles (set to 0)
        $brandData = [];
        foreach ($allVehicles as $vehicle) {
            $plateNumber = $vehicle->plate_number ?? 'N/A';
            $brandData[$plateNumber] = 0;
        }
        
        // Count bookings for each vehicle
        foreach ($brandBookings as $booking) {
            $vehicle = $booking->vehicle;
            if (!$vehicle) continue;
            
            // Filter by vehicle type if specified
            if ($brandVehicleType !== 'all') {
                if ($brandVehicleType === 'car' && !($vehicle instanceof Car)) continue;
                if ($brandVehicleType === 'motorcycle' && !($vehicle instanceof Motorcycle)) continue;
            }
            
            $plateNumber = $vehicle->plate_number ?? 'N/A';
            if (isset($brandData[$plateNumber])) {
                $brandData[$plateNumber]++;
            } else {
                // If vehicle not in active list, add it
                $brandData[$plateNumber] = 1;
            }
        }
        
        // Sort by plate number (alphabetically)
        ksort($brandData);

        // Comparison data (last 4 months)
        $comparisonData = [];
        for ($i = 3; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();
            
            $total = Booking::whereBetween('rental_start_date', [$monthStart, $monthEnd])
                ->where('booking_status', '!=', 'Cancelled')
                ->count();
            
            $cars = Booking::whereBetween('rental_start_date', [$monthStart, $monthEnd])
                ->where('booking_status', '!=', 'Cancelled')
                ->with('vehicle')
                ->get()
                ->filter(function($booking) {
                    return $booking->vehicle instanceof Car;
                })
                ->count();
            
            $motorcycles = Booking::whereBetween('rental_start_date', [$monthStart, $monthEnd])
                ->where('booking_status', '!=', 'Cancelled')
                ->with('vehicle')
                ->get()
                ->filter(function($booking) {
                    return $booking->vehicle instanceof Motorcycle;
                })
                ->count();
            
            $comparisonData[] = [
                'month' => $month->format('M Y'),
                'total' => $total,
                'cars' => $cars,
                'motorcycles' => $motorcycles,
            ];
        }

        // Generate PDF based on active tab
        $pdf = Pdf::loadView('admin.charts.pdf', [
            'activeTab' => $activeTab,
            'selectedWeek' => $selectedWeek,
            'selectedMonth' => $selectedMonth,
            'facultyMonth' => $facultyMonth,
            'facultyYear' => $facultyYear,
            'brandMonth' => $brandMonth,
            'brandVehicleType' => $brandVehicleType,
            'weeklyData' => $weeklyData,
            'monthlyData' => $monthlyData,
            'facultyData' => $facultyData,
            'brandData' => $brandData,
            'comparisonData' => $comparisonData,
            'date' => Carbon::now()->format('Y-m-d'),
        ]);

        return $pdf->download('charts-report-' . date('Y-m-d') . '.pdf');
    }

    /**
     * Export charts as Excel (CSV)
     */
    public function exportExcel(Request $request)
    {
        $activeTab = $request->get('tab', 'weekly');
        $data = [];

        if ($activeTab === 'weekly') {
            $selectedWeek = $request->get('selected_week', Carbon::now()->format('Y-\WW'));
            $weekParts = explode('-W', $selectedWeek);
            $weekStart = Carbon::now()->setISODate($weekParts[0], $weekParts[1] ?? 1)->startOfWeek();
            $weekEnd = $weekStart->copy()->endOfWeek();
            
            for ($date = $weekStart->copy(); $date <= $weekEnd; $date->addDay()) {
                $count = Booking::whereDate('rental_start_date', $date->format('Y-m-d'))
                    ->where('booking_status', '!=', 'Cancelled')
                    ->count();
                $data[] = [
                    'Date' => $date->format('Y-m-d'),
                    'Day' => $date->format('l'),
                    'Rental Count' => $count,
                ];
            }
        } elseif ($activeTab === 'monthly') {
            $selectedMonth = $request->get('selected_month', Carbon::now()->format('Y-m'));
            $monthParts = explode('-', $selectedMonth);
            $monthStart = Carbon::create($monthParts[0], $monthParts[1], 1);
            $daysInMonth = $monthStart->daysInMonth;
            
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = Carbon::create($monthParts[0], $monthParts[1], $day);
                $count = Booking::whereDate('rental_start_date', $date->format('Y-m-d'))
                    ->where('booking_status', '!=', 'Cancelled')
                    ->count();
                $data[] = [
                    'Date' => $date->format('Y-m-d'),
                    'Day' => $day,
                    'Rental Count' => $count,
                ];
            }
        } elseif ($activeTab === 'faculty') {
            $facultyMonth = $request->get('faculty_month', Carbon::now()->format('Y-m'));
            $facultyMonthParts = explode('-', $facultyMonth);
            $facultyMonthStart = Carbon::create($facultyMonthParts[0], $facultyMonthParts[1], 1)->startOfMonth();
            $facultyMonthEnd = $facultyMonthStart->copy()->endOfMonth();
            
            $bookings = Booking::whereBetween('rental_start_date', [$facultyMonthStart, $facultyMonthEnd])
                ->where('booking_status', '!=', 'Cancelled')
                ->with(['customer.localStudent.studentDetails', 'customer.internationalStudent.studentDetails'])
                ->get();
            
            // Track unique customers per faculty
            $facultyCustomers = [];
            foreach ($bookings as $booking) {
                if (!$booking->customer) continue;
                
                $faculty = 'Unknown';
                if ($booking->customer->localStudent && $booking->customer->localStudent->studentDetails) {
                    $faculty = $booking->customer->localStudent->studentDetails->faculty ?? 'Unknown';
                } elseif ($booking->customer->internationalStudent && $booking->customer->internationalStudent->studentDetails) {
                    $faculty = $booking->customer->internationalStudent->studentDetails->faculty ?? 'Unknown';
                }
                
                if (!isset($facultyCustomers[$faculty])) {
                    $facultyCustomers[$faculty] = [];
                }
                $customerID = $booking->customer->customerID;
                if (!in_array($customerID, $facultyCustomers[$faculty])) {
                    $facultyCustomers[$faculty][] = $customerID;
                }
            }
            
            foreach ($facultyCustomers as $faculty => $customerIDs) {
                $data[] = [
                    'Faculty' => $faculty,
                    'Number of People' => count($customerIDs),
                ];
            }
        } elseif ($activeTab === 'brand') {
            $brandMonth = $request->get('brand_month', Carbon::now()->format('Y-m'));
            $brandMonthParts = explode('-', $brandMonth);
            $brandMonthStart = Carbon::create($brandMonthParts[0], $brandMonthParts[1], 1)->startOfMonth();
            $brandMonthEnd = $brandMonthStart->copy()->endOfMonth();
            
            // Get ALL active vehicles
            $allVehicles = Vehicle::where('isActive', true)
                ->orderBy('plate_number')
                ->get();
            
            // Get bookings for the selected month
            $brandBookings = Booking::whereBetween('rental_start_date', [$brandMonthStart, $brandMonthEnd])
                ->where('booking_status', '!=', 'Cancelled')
                ->with('vehicle')
                ->get();
            
            // Initialize with all vehicles (set to 0)
            $brandCounts = [];
            foreach ($allVehicles as $vehicle) {
                $plateNumber = $vehicle->plate_number ?? 'N/A';
                $brandCounts[$plateNumber] = 0;
            }
            
            // Count bookings for each vehicle
            foreach ($brandBookings as $booking) {
                $vehicle = $booking->vehicle;
                if (!$vehicle) continue;
                
                $plateNumber = $vehicle->plate_number ?? 'N/A';
                if (isset($brandCounts[$plateNumber])) {
                    $brandCounts[$plateNumber]++;
                } else {
                    // If vehicle not in active list, add it
                    $brandCounts[$plateNumber] = 1;
                }
            }
            
            // Sort by plate number (alphabetically)
            ksort($brandCounts);
            
            foreach ($brandCounts as $plateNumber => $count) {
                $data[] = [
                    'Vehicle Plate Number' => $plateNumber,
                    'Booking Count' => $count,
                ];
            }
        } else {
            // Comparison data
            for ($i = 3; $i >= 0; $i--) {
                $month = Carbon::now()->subMonths($i);
                $monthStart = $month->copy()->startOfMonth();
                $monthEnd = $month->copy()->endOfMonth();
                
                $total = Booking::whereBetween('rental_start_date', [$monthStart, $monthEnd])
                    ->where('booking_status', '!=', 'Cancelled')
                    ->count();
                
                $cars = Booking::whereBetween('rental_start_date', [$monthStart, $monthEnd])
                    ->where('booking_status', '!=', 'Cancelled')
                    ->with('vehicle')
                    ->get()
                    ->filter(function($booking) {
                        return $booking->vehicle instanceof Car;
                    })
                    ->count();
                
                $motorcycles = Booking::whereBetween('rental_start_date', [$monthStart, $monthEnd])
                    ->where('booking_status', '!=', 'Cancelled')
                    ->with('vehicle')
                    ->get()
                    ->filter(function($booking) {
                        return $booking->vehicle instanceof Motorcycle;
                    })
                    ->count();
                
                $data[] = [
                    'Month' => $month->format('M Y'),
                    'Total' => $total,
                    'Cars' => $cars,
                    'Motorcycles' => $motorcycles,
                ];
            }
        }

        $filename = 'charts-export-' . $activeTab . '-' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            if (!empty($data)) {
                fputcsv($file, array_keys($data[0]));
            }
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
