<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        
        // Get active bookings (bookings that are currently ongoing)
        $activeBookings = Booking::where('rental_start_date', '<=', $today)
            ->where('rental_end_date', '>=', $today)
            ->count();

        // Get total bookings count
        $totalBookings = Booking::count();

        // Get today's pickups and returns
        $todayPickups = Booking::whereDate('rental_start_date', $today)->count();
        $todayReturns = Booking::whereDate('rental_end_date', $today)->count();

        // Get payments data
        $pendingPayments = Payment::where('payment_status', 'Pending')
            ->with(['booking.customer.user', 'booking.vehicle'])
            ->orderByDesc('payment_date')
            ->take(5)
            ->get();
            
        $verifiedPayments = Payment::where('payment_status', 'Verified')
            ->whereDate('payment_date', $today)
            ->count();

        // Get recent bookings
        $recentBookings = Booking::with(['customer.user', 'vehicle'])
            ->orderByDesc('lastUpdateDate')
            ->take(5)
            ->get();

        // Calculate revenue for the current month
        $currentMonthRevenue = Payment::where('payment_status', 'Verified')
            ->whereMonth('payment_date', $today->month)
            ->whereYear('payment_date', $today->year)
            ->sum('total_amount');

        // Get vehicles data
        $totalVehicles = Vehicle::count();
        $vehiclesInMaintenance = Vehicle::where('availability_status', 'maintenance')->count();
        
        // Get available vehicles count (not in maintenance and not booked)
        $availableVehicles = Vehicle::where('availability_status', '!=', 'maintenance')
            ->whereDoesntHave('bookings', function($query) use ($today) {
                $query->where('booking_status', '!=', 'Cancelled')
                      ->where('rental_start_date', '<=', $today)
                      ->where('rental_end_date', '>=', $today);
            })->count();
            
        $vehiclesRented = $totalVehicles - $availableVehicles - $vehiclesInMaintenance;

        // Get total customers count
        $totalCustomers = User::count();
        
        // Get recent payments for the dashboard
        $recentPayments = Payment::with(['booking.customer.user', 'booking.vehicle'])
            ->orderByDesc('payment_date')
            ->take(5)
            ->get();
            
        // Get monthly revenue for the last 6 months
        $monthlyRevenue = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $revenue = Payment::where('payment_status', 'Verified')
                ->whereMonth('payment_date', $date->month)
                ->whereYear('payment_date', $date->year)
                ->sum('total_amount');
                
            $monthlyRevenue[] = [
                'label' => $date->format('M Y'),
                'total' => $revenue
            ];
        }

        return view('dashboard', [
            'today' => Carbon::now(),
            'metrics' => [
                'activeBookings' => $activeBookings,
                'totalBookings' => $totalBookings,
                'todayPickups' => $todayPickups,
                'todayReturns' => $todayReturns,
                'pendingPayments' => $pendingPayments->count(),
                'verifiedPayments' => $verifiedPayments,
                'currentMonthRevenue' => $currentMonthRevenue,
                'availableVehicles' => $availableVehicles,
                'vehiclesRented' => $vehiclesRented,
                'vehiclesMaintenance' => $vehiclesInMaintenance,
                'totalVehicles' => $totalVehicles,
                'totalCustomers' => $totalCustomers,
            ],
            'pendingPayments' => $pendingPayments,
            'recentPayments' => $recentPayments,
            'recentBookings' => $recentBookings,
            'monthlyRevenue' => $monthlyRevenue,
        ]);
    }
}
