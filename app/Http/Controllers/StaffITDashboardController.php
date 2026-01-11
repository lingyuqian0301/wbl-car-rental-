<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\View\View;

class StaffITDashboardController extends Controller
{
    public function __invoke(): View
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Calculate current day available fleet (vehicles not booked today)
        $currentDayAvailableFleet = Vehicle::where('availability_status', '!=', 'maintenance')
            ->whereDoesntHave('bookings', function($query) use ($today) {
                $query->where('booking_status', '!=', 'Cancelled')
                      ->where('rental_start_date', '<=', $today)
                      ->where('rental_end_date', '>=', $today);
            })->count();

        // Calculate new bookings in current week (Monday to Sunday)
        $startOfWeek = $today->copy()->startOfWeek(Carbon::MONDAY);
        $endOfWeek = $today->copy()->endOfWeek(Carbon::SUNDAY);
        $newBookingsThisWeek = Booking::whereBetween('lastUpdateDate', [$startOfWeek, $endOfWeek])
            ->count();

        // Count payments where isVerify is false
        $unverifiedPayments = Payment::where('payment_isVerify', false)
            ->orWhere('payment_isVerify', 0)
            ->orWhereNull('payment_isVerify')
            ->count();

        // Count today's pickup bookings
        $todayPickupBookings = Booking::whereDate('rental_start_date', $today)
            ->whereIn('booking_status', ['Pending', 'Confirmed'])
            ->count();

        // Count today's return bookings
        $todayReturnBookings = Booking::whereDate('rental_end_date', $today)
            ->whereIn('booking_status', ['Pending', 'Confirmed', 'Completed'])
            ->count();

        $metrics = [
            'totalBookings' => Booking::count(),
            'activeBookings' => Booking::whereIn('booking_status', ['Pending', 'Confirmed'])->count(),
            'newBookingsThisWeek' => $newBookingsThisWeek,
            'completedBookings' => Booking::where('booking_status', 'Completed')->count(),
            'pendingPayments' => Payment::where('payment_status', 'Pending')->count(),
            'unverifiedPayments' => $unverifiedPayments,
            'verifiedPayments' => Payment::where('payment_status', 'Verified')->count(),
            // Use 'availability_status' instead of 'status'
            'vehiclesAvailable'   => Vehicle::where('availability_status', 'available')->count(),
            'vehiclesRented'      => Vehicle::where('availability_status', 'rented')->count(),
            'vehiclesMaintenance' => Vehicle::where('availability_status', 'maintenance')->count(),
            'fleetTotal' => Vehicle::count(),
            'currentDayAvailableFleet' => $currentDayAvailableFleet,
            'todayPickupBookings' => $todayPickupBookings,
            'todayReturnBookings' => $todayReturnBookings,
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
            $upcomingBookingsToServe = Booking::with(['vehicle', 'customer.user', 'payments'])
                ->whereIn('booking_status', ['Pending', 'Confirmed'])
                ->whereBetween('rental_start_date', [$today, $today->copy()->addDays(3)])
                ->orderBy('rental_start_date', 'asc')
                ->take(10)
                ->get()
                ->map(function($booking) {
                    // Calculate payment status (Deposit or Full)
                    $totalPaid = $booking->payments->where('payment_status', 'Verified')->sum('total_amount');
                    $totalRequired = ($booking->rental_amount ?? 0) + ($booking->deposit_amount ?? 0);
                    $paymentStatus = $totalPaid >= $totalRequired ? 'Full' : 'Deposit';
                    $booking->payment_status_display = $paymentStatus;
                    return $booking;
                });
        } catch (\Exception $e) {
            $upcomingBookingsToServe = collect([]);
        }

        // Cancellation requests with refund status false
        $cancellationRequests = Booking::with(['vehicle', 'customer.user', 'payments'])
            ->whereIn('booking_status', ['request cancelling', 'refunding', 'Cancelled', 'cancelled'])
            ->whereDoesntHave('payments', function($query) {
                $query->where('payment_status', 'Refunded');
            })
            ->orderByDesc('lastUpdateDate')
            ->take(5)
            ->get();

        // Weekly booking statistics for Fleet Booking (Monday to Sunday)
        $yesterday = $today->copy()->subDay();

        $weeklyBookings = Booking::whereBetween('rental_start_date', [$startOfWeek, $endOfWeek])
            ->whereIn('booking_status', ['Pending', 'Confirmed', 'Completed'])
            ->get();

        $weeklyBookingStats = [
            'done' => $weeklyBookings->filter(function ($booking) use ($yesterday) {
                return $booking->rental_start_date->lessThanOrEqualTo($yesterday);
            })->count(),
            'current' => $weeklyBookings->filter(function ($booking) use ($today) {
                return $booking->rental_start_date->isSameDay($today);
            })->count(),
            'upcoming' => $weeklyBookings->filter(function ($booking) use ($today) {
                return $booking->rental_start_date->greaterThan($today);
            })->count(),
            'total' => $weeklyBookings->count(),
        ];

        // Booking need runner: future bookings where pickup_point or return_point is not 'HASTA HQ Office'
        $bookingsNeedRunner = Booking::with(['vehicle', 'customer.user'])
            ->where('rental_start_date', '>', $today)
            ->whereIn('booking_status', ['Pending', 'Confirmed'])
            ->where(function($query) {
                $query->where(function($q) {
                    $q->whereNotNull('pickup_point')
                      ->where('pickup_point', '!=', '')
                      ->where('pickup_point', '!=', 'HASTA HQ Office');
                })->orWhere(function($q) {
                    $q->whereNotNull('return_point')
                      ->where('return_point', '!=', '')
                      ->where('return_point', '!=', 'HASTA HQ Office');
                });
            })
            ->orderBy('rental_start_date', 'asc')
            ->take(10)
            ->get()
            ->map(function($booking) {
                $booking->assigned_status = $booking->staff_served ? 'assigned' : 'unassigned';
                return $booking;
            });

        return view('staffit.dashboard', [
            'metrics' => $metrics,
            'recentBookings' => $recentBookings,
            'recentPayments' => $recentPayments,
            'pendingPayments' => $pendingPayments,
            'upcomingBookingsToServe' => $upcomingBookingsToServe,
            'cancellationRequests' => $cancellationRequests,
            'bookingsNeedRunner' => $bookingsNeedRunner,
            'today' => $today,
            'weeklyBookingStats' => $weeklyBookingStats,
            'startOfMonth' => $startOfMonth,
            'endOfMonth' => $endOfMonth,
            'startOfWeek' => $startOfWeek,
            'endOfWeek' => $endOfWeek,
            'isStaffIT' => true,
        ]);
    }
}


