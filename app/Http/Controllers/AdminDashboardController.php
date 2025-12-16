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
            'activeBookings' => Booking::whereIn('status', ['Pending', 'Confirmed'])->count(),
            'completedBookings' => Booking::where('status', 'Completed')->count(),
            'pendingPayments' => Payment::where('status', 'Pending')->count(),
            'verifiedPayments' => Payment::where('status', 'Verified')->count(),
            'revenueAllTime' => Payment::where('status', 'Verified')->sum('amount'),
            'revenueThisMonth' => Payment::where('status', 'Verified')
                ->whereBetween('payment_date', [$startOfMonth, $endOfMonth])
                ->sum('amount'),
            'vehiclesAvailable' => Vehicle::where('status', 'Available')->count(),
            'vehiclesRented' => Vehicle::where('status', 'Rented')->count(),
            'vehiclesMaintenance' => Vehicle::where('status', 'Maintenance')->count(),
            'fleetTotal' => Vehicle::count(),
        ];

        $recentBookings = Booking::with(['vehicle', 'user'])
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        $recentPayments = Payment::with(['booking.vehicle', 'booking.user'])
            ->orderByDesc('payment_date')
            ->take(5)
            ->get();

        $pendingPayments = Payment::with(['booking.vehicle', 'booking.user'])
            ->where('status', 'Pending')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        return view('admin.dashboard', [
            'metrics' => $metrics,
            'recentBookings' => $recentBookings,
            'recentPayments' => $recentPayments,
            'pendingPayments' => $pendingPayments,
            'monthlyRevenue' => $this->monthlyRevenueData(),
            'today' => $today,
        ]);
    }

    /**
     * Build a six-month revenue trend (including current month).
     */
    private function monthlyRevenueData(): array
    {
        $months = collect(range(0, 5))
            ->map(fn (int $i) => Carbon::now()->subMonths($i)->startOfMonth())
            ->reverse()
            ->values();

        $payments = Payment::where('status', 'Verified')
            ->where('payment_date', '>=', $months->first()->copy()->startOfMonth())
            ->get()
            ->groupBy(fn ($payment) => Carbon::parse($payment->payment_date)->format('Y-m'));

        return $months->map(function (Carbon $month) use ($payments) {
            $key = $month->format('Y-m');
            $label = $month->format('M');
            $total = ($payments->get($key)?->sum('amount')) ?? 0;

            return [
                'label' => $label,
                'total' => round((float) $total, 2),
            ];
        })->all();
    }
}



