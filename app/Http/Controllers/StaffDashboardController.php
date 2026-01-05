<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Vehicle;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\View\View;

class StaffDashboardController extends Controller
{
    public function __invoke(): View
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $metrics = [
            'totalBookings' => Booking::count(),
            'activeBookings' => Booking::whereIn('booking_status', ['Pending', 'Confirmed'])->count(),
            'completedBookings' => Booking::where('booking_status', 'Completed')->count(),
            'pendingPayments' => Payment::where('payment_status', 'Pending')->count(),
            'verifiedPayments' => Payment::where('payment_status', 'Verified')->count(),
            // Vehicle metrics - using 'availability_status' instead of 'status'
            'vehiclesAvailable' => Vehicle::where('availability_status', 'available')->count(),
            'vehiclesRented' => Vehicle::where('availability_status', 'rented')->count(),
            'vehiclesMaintenance' => Vehicle::where('availability_status', 'maintenance')->count(),
            'fleetTotal' => Vehicle::count(),
        ];

        $recentBookings = Booking::with(['vehicle', 'customer.user'])
            ->orderByDesc('lastUpdateDate')
            ->take(5)
            ->get();

        $recentPayments = Payment::with(['booking.vehicle', 'booking.customer.user'])
            ->orderByDesc('payment_date')
            ->take(5)
            ->get();

        $pendingPayments = Payment::with(['booking.vehicle', 'booking.customer.user'])
            ->where('payment_status', 'Pending')
            ->orderByDesc('payment_date')
            ->take(5)
            ->get();

        // Upcoming bookings to serve (pickup date within next 3 days)
        try {
            $upcomingBookingsToServe = Booking::with(['vehicle', 'customer.user'])
                ->whereIn('booking_status', ['Pending', 'Confirmed'])
                ->whereBetween('rental_start_date', [$today, $today->copy()->addDays(3)])
                ->orderBy('rental_start_date', 'asc')
                ->take(10)
                ->get();
        } catch (\Exception $e) {
            $upcomingBookingsToServe = collect([]);
        }

        return view('admin.dashboard', [
            'metrics' => $metrics,
            'recentBookings' => $recentBookings,
            'recentPayments' => $recentPayments,
            'pendingPayments' => $pendingPayments,
            'upcomingBookingsToServe' => $upcomingBookingsToServe,
            'monthlyRevenue' => [], // Empty array for staff - no revenue data
            'today' => $today,
            'isStaff' => true, // Flag to indicate this is staff view
        ]);
    }
}






