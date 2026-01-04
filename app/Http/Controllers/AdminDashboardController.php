<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\View\View;

class AdminDashboardController extends Controller
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
            'revenueAllTime' => Payment::where('payment_status', 'Verified')->sum('total_amount'),
            'revenueThisMonth' => Payment::where('payment_status', 'Verified')
                ->whereBetween('payment_date', [$startOfMonth, $endOfMonth])
                ->sum('total_amount'),
            // Use 'availability_status' instead of 'status'
'vehiclesAvailable'   => Vehicle::where('availability_status', 'available')->count(),
'vehiclesRented'      => Vehicle::where('availability_status', 'rented')->count(),
'vehiclesMaintenance' => Vehicle::where('availability_status', 'maintenance')->count(),
            'fleetTotal' => Vehicle::count(),
        ];

        $recentBookings = Booking::with(['vehicle', 'customer.user'])
            ->orderByDesc('created_at')
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
            'monthlyRevenue' => $this->monthlyRevenueData(),
            'today' => $today,
        ]);
    }

    /**
     * Build a three-month revenue trend (including current month).
     */
    private function monthlyRevenueData(): array
    {
        $months = collect(range(0, 2))
            ->map(fn (int $i) => Carbon::now()->subMonths($i)->startOfMonth())
            ->reverse()
            ->values();

        $payments = Payment::where('payment_status', 'Verified')
            ->where('payment_date', '>=', $months->first()->copy()->startOfMonth())
            ->get()
            ->groupBy(fn ($payment) => Carbon::parse($payment->payment_date)->format('Y-m'));

        return $months->map(function (Carbon $month) use ($payments) {
            $key = $month->format('Y-m');
            $label = $month->format('M');
            $total = ($payments->get($key)?->sum('total_amount')) ?? 0;

            return [
                'label' => $label,
                'total' => round((float) $total, 2),
            ];
        })->all();
    }
}



