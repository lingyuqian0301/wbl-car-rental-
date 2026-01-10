<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Vehicle;
use App\Models\VehicleMaintenance;
use App\Models\Fuel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(): View
    {
        // Redirect StaffIT users to their dashboard
        if (auth()->check() && auth()->user()->isStaffIT()) {
            return redirect()->route('staffit.dashboard');
        }

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
            'revenueAllTime' => Payment::where('payment_status', 'Verified')->sum('total_amount'),
            'revenueThisMonth' => Payment::where('payment_status', 'Verified')
                ->whereBetween('payment_date', [$startOfMonth, $endOfMonth])
                ->sum('total_amount'),
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
        // Refund status false means bookings that haven't been refunded yet
        $cancellationRequests = Booking::with(['vehicle', 'customer.user', 'payments'])
            ->whereIn('booking_status', ['request cancelling', 'refunding', 'Cancelled', 'cancelled'])
            ->whereDoesntHave('payments', function($query) {
                // Refund status false means no refund payment exists (payment_status = 'Refunded')
                $query->where('payment_status', 'Refunded');
            })
            ->orderByDesc('lastUpdateDate')
            ->take(5)
            ->get();

        // Weekly booking statistics for Fleet Booking (Monday to Sunday)
        // $startOfWeek and $endOfWeek already calculated above
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
                    $q->where('pickup_point', '!=', 'HASTA HQ Office')
                      ->orWhereNull('pickup_point');
                })->orWhere(function($q) {
                    $q->where('return_point', '!=', 'HASTA HQ Office')
                      ->orWhereNull('return_point');
                });
            })
            ->orderBy('rental_start_date', 'asc')
            ->take(10)
            ->get()
            ->map(function($booking) {
                // Determine assigned status based on staff_served (runner)
                $booking->assigned_status = $booking->staff_served ? 'assigned' : 'unassigned';
                return $booking;
            });

        return view('admin.dashboard', [
            'metrics' => $metrics,
            'recentBookings' => $recentBookings,
            'recentPayments' => $recentPayments,
            'pendingPayments' => $pendingPayments,
            'upcomingBookingsToServe' => $upcomingBookingsToServe,
            'cancellationRequests' => $cancellationRequests,
            'bookingsNeedRunner' => $bookingsNeedRunner,
            'monthlyRevenue' => $this->monthlyRevenueData(),
            'today' => $today,
            'weeklyBookingStats' => $weeklyBookingStats,
            'startOfMonth' => $startOfMonth,
            'endOfMonth' => $endOfMonth,
            'startOfWeek' => $startOfWeek,
            'endOfWeek' => $endOfWeek,
        ]);
    }

    /**
     * Build a three-month revenue trend showing payment amount received.
     * 1. Payment amount (excluding deposit payments)
     * 2. Deposit minus refund (deposit payments - refunded amounts)
     */
    private function monthlyRevenueData(): array
    {
        $months = collect(range(0, 2))
            ->map(fn (int $i) => Carbon::now()->subMonths($i)->startOfMonth())
            ->reverse()
            ->values();

        return $months->map(function (Carbon $month) {
            $dateFrom = $month->copy()->startOfMonth();
            $dateTo = $month->copy()->endOfMonth();
            $label = $month->format('M');

            // Get verified payments for this month with booking relationship
            $verifiedPayments = Payment::with('booking')
                ->where('payment_status', 'Verified')
                ->whereBetween('payment_date', [$dateFrom, $dateTo])
                ->get();

            // 1. Payment amounts (excluding deposit payments)
            // A deposit payment is one where the payment amount exactly matches the booking's deposit_amount
            $regularPaymentAmount = $verifiedPayments->filter(function($payment) {
                if (!$payment->booking) return true;
                $paymentAmount = $payment->total_amount ?? 0;
                $depositAmount = $payment->booking->deposit_amount ?? 0;
                // If amounts match exactly (within 0.01 tolerance), it's a deposit
                return abs($paymentAmount - $depositAmount) >= 0.01;
            })->sum('total_amount');

            // 2. Deposit minus refund
            // Get deposit payments (payments where amount matches booking deposit_amount)
            $depositPayments = $verifiedPayments->filter(function($payment) {
                if (!$payment->booking) return false;
                $paymentAmount = $payment->total_amount ?? 0;
                $depositAmount = $payment->booking->deposit_amount ?? 0;
                return abs($paymentAmount - $depositAmount) < 0.01;
            });
            
            $depositAmount = $depositPayments->sum('total_amount');
            
            // Get refunded amounts for this month (refunds are negative amounts or status 'Refunded')
            $refundedAmount = Payment::where('payment_status', 'Refunded')
                ->whereBetween('payment_date', [$dateFrom, $dateTo])
                ->sum(DB::raw('ABS(total_amount)'));
            
            // Deposit minus refund (deposit fine might be deducted, but refund is what we subtract)
            $depositNetAmount = max(0, $depositAmount - $refundedAmount);

            // Total payment amount received = regular payments + (deposit - refund)
            $totalPaymentReceived = $regularPaymentAmount + $depositNetAmount;

            return [
                'label' => $label,
                'total' => round((float) $totalPaymentReceived, 2),
                'month' => $month->month,
                'year' => $month->year,
                'date_from' => $dateFrom->format('Y-m-d'),
                'date_to' => $dateTo->format('Y-m-d'),
            ];
        })->all();
    }
}



