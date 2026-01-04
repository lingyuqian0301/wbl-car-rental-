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

        // Faculty rental data
        $facultyMonth = $request->get('faculty_month', Carbon::now()->format('Y-m'));
        $facultyYear = $request->get('faculty_year', Carbon::now()->year);
        $facultyMonthParts = explode('-', $facultyMonth);
        $facultyMonthStart = Carbon::create($facultyMonthParts[0], $facultyMonthParts[1], 1)->startOfMonth();
        $facultyMonthEnd = $facultyMonthStart->copy()->endOfMonth();
        
        $facultyData = [];
        // Get bookings and join with customer -> studentdetails to get faculty
        $bookings = Booking::whereBetween('rental_start_date', [$facultyMonthStart, $facultyMonthEnd])
            ->where('booking_status', '!=', 'Cancelled')
            ->with(['customer.studentDetail'])
            ->get();
        
        foreach ($bookings as $booking) {
            if ($booking->customer && $booking->customer->studentDetail) {
                $faculty = $booking->customer->studentDetail->faculty ?? 'Unknown';
                if (!isset($facultyData[$faculty])) {
                    $facultyData[$faculty] = 0;
                }
                $facultyData[$faculty]++;
            } else {
                $faculty = 'Unknown';
                if (!isset($facultyData[$faculty])) {
                    $facultyData[$faculty] = 0;
                }
                $facultyData[$faculty]++;
            }
        }

        // Brand rental data
        $brandMonth = $request->get('brand_month', Carbon::now()->format('Y-m'));
        $brandVehicleType = $request->get('brand_vehicle_type', 'all');
        $brandMonthParts = explode('-', $brandMonth);
        $brandMonthStart = Carbon::create($brandMonthParts[0], $brandMonthParts[1], 1)->startOfMonth();
        $brandMonthEnd = $brandMonthStart->copy()->endOfMonth();
        
        $brandData = [];
        $brandBookings = Booking::whereBetween('rental_start_date', [$brandMonthStart, $brandMonthEnd])
            ->where('booking_status', '!=', 'Cancelled')
            ->with('vehicle')
            ->get();
        
        foreach ($brandBookings as $booking) {
            $vehicle = $booking->vehicle;
            if (!$vehicle) continue;
            
            // Filter by vehicle type
            if ($brandVehicleType !== 'all') {
                if ($brandVehicleType === 'car' && !($vehicle instanceof Car)) continue;
                if ($brandVehicleType === 'motorcycle' && !($vehicle instanceof Motorcycle)) continue;
            }
            
            $brand = $vehicle->vehicle_brand ?? 'Unknown';
            if (!isset($brandData[$brand])) {
                $brandData[$brand] = 0;
            }
            $brandData[$brand]++;
        }

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

        // Generate PDF based on active tab
        $pdf = Pdf::loadView('admin.charts.pdf', [
            'activeTab' => $activeTab,
            'selectedWeek' => $selectedWeek,
            'selectedMonth' => $selectedMonth,
            'date' => Carbon::now()->format('Y-m-d'),
        ]);

        return $pdf->download('charts-report-' . date('Y-m-d') . '.pdf');
    }
}
