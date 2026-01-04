<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Car;
use App\Models\Motorcycle;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\View\View;

class StaffDashboardController extends Controller
{
    public function __invoke(): View
    {
        $today = Carbon::today();

        $metrics = [
            'totalBookings' => Booking::count(),
            'activeBookings' => Booking::whereIn('booking_status', ['Pending', 'Confirmed'])->count(),
            'completedBookings' => Booking::where('booking_status', 'Completed')->count(),
            'pendingPayments' => Payment::where('payment_status', 'Pending')->count(),
            'verifiedPayments' => Payment::where('payment_status', 'Verified')->count(),
            // No revenue metrics for staff
            'availability_status' => Car::where('availability_status', 'Available')->count() + 
                                   Motorcycle::where('availability_status', 'Available')->count(), 
            'vehiclesRented' => Car::where('availability_status', 'Rented')->count() + 
                                Motorcycle::where('availability_status', 'Rented')->count(),
            'vehiclesMaintenance' => Car::where('availability_status', 'Maintenance')->count() + 
                                     Motorcycle::where('availability_status', 'Maintenance')->count(),
            'fleetTotal' => Car::count() + Motorcycle::count(),
            'doneBookings' => Booking::where('booking_status', 'Completed')
                ->where('rental_end_date', '<', $today)
                ->count(),
            'todayBookings' => Booking::whereIn('booking_status', ['Pending', 'Confirmed'])
                ->whereDate('rental_start_date', $today)
                ->count(),
            'tomorrowBookings' => Booking::whereIn('booking_status', ['Pending', 'Confirmed'])
                ->whereDate('rental_start_date', $today->copy()->addDay())
                ->count(),
            'weekBookings' => Booking::whereIn('booking_status', ['Pending', 'Confirmed'])
                ->whereBetween('rental_start_date', [$today, $today->copy()->addDays(7)])
                ->count(),
        ];

        $recentBookings = Booking::with(['user'])
            ->orderByDesc('creationDate')
            ->take(5)
            ->get();

        $recentPayments = Payment::with(['booking.user'])
            ->orderBy('payment_date', 'desc')
            ->take(5)
            ->get();

        $pendingPayments = Payment::with(['booking.user'])
            ->where('payment_status', 'Pending')
            ->orderBy('payment_date', 'desc')
            ->take(5)
            ->get();

        return view('staff.dashboard', [
            'metrics' => $metrics,
            'recentBookings' => $recentBookings,
            'recentPayments' => $recentPayments,
            'pendingPayments' => $pendingPayments,
            'today' => $today,
        ]);
    }
}






