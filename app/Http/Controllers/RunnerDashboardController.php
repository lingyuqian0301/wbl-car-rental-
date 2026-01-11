<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RunnerDashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $today = Carbon::today();
        
        // Get runner's assigned tasks stats
        $totalTasks = $this->getRunnerTasksQuery($user->userID)->count();
        $upcomingTasks = $this->getRunnerTasksQuery($user->userID)
            ->where('rental_start_date', '>', $today)
            ->count();
        $doneTasks = $this->getRunnerTasksQuery($user->userID)
            ->where('rental_end_date', '<', $today)
            ->count();
        
        // Get today's tasks
        $todayTasks = $this->getRunnerTasksQuery($user->userID)
            ->where(function($q) use ($today) {
                $q->whereDate('rental_start_date', $today)
                  ->orWhereDate('rental_end_date', $today);
            })
            ->get();
        
        // Calculate total commission for current month
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        $monthlyCommission = $this->calculateMonthlyCommission($user->userID, $currentMonth, $currentYear);
        
        return view('runner.dashboard', [
            'user' => $user,
            'today' => $today,
            'totalTasks' => $totalTasks,
            'upcomingTasks' => $upcomingTasks,
            'doneTasks' => $doneTasks,
            'todayTasks' => $todayTasks,
            'monthlyCommission' => $monthlyCommission,
        ]);
    }
    
    private function getRunnerTasksQuery($userId)
    {
        return Booking::with(['vehicle', 'customer.user'])
            ->where('staff_served', $userId)
            ->where(function($q) {
                $q->where(function($subQ) {
                    $subQ->whereNotNull('pickup_point')
                         ->where('pickup_point', '!=', '')
                         ->where('pickup_point', '!=', 'HASTA HQ Office');
                })->orWhere(function($subQ) {
                    $subQ->whereNotNull('return_point')
                         ->where('return_point', '!=', '')
                         ->where('return_point', '!=', 'HASTA HQ Office');
                });
            });
    }
    
    private function calculateMonthlyCommission($userId, $month, $year)
    {
        $bookings = Booking::where('staff_served', $userId)
            ->where(function($q) {
                $q->where(function($subQ) {
                    $subQ->whereNotNull('pickup_point')
                         ->where('pickup_point', '!=', '')
                         ->where('pickup_point', '!=', 'HASTA HQ Office');
                })->orWhere(function($subQ) {
                    $subQ->whereNotNull('return_point')
                         ->where('return_point', '!=', '')
                         ->where('return_point', '!=', 'HASTA HQ Office');
                });
            })
            ->where(function($q) use ($month, $year) {
                $q->where(function($dateQ) use ($month, $year) {
                    $dateQ->whereMonth('rental_start_date', $month)
                          ->whereYear('rental_start_date', $year);
                })->orWhere(function($dateQ) use ($month, $year) {
                    $dateQ->whereMonth('rental_end_date', $month)
                          ->whereYear('rental_end_date', $year);
                });
            })
            ->get();
        
        $commission = 0;
        foreach ($bookings as $booking) {
            $pickupDate = $booking->rental_start_date ? Carbon::parse($booking->rental_start_date) : null;
            $returnDate = $booking->rental_end_date ? Carbon::parse($booking->rental_end_date) : null;
            
            // RM2 per pickup if not at HASTA HQ Office
            if ($pickupDate && $pickupDate->month == $month && $pickupDate->year == $year) {
                if (!empty($booking->pickup_point) && $booking->pickup_point !== 'HASTA HQ Office') {
                    $commission += 2;
                }
            }
            
            // RM2 per return if not at HASTA HQ Office
            if ($returnDate && $returnDate->month == $month && $returnDate->year == $year) {
                if (!empty($booking->return_point) && $booking->return_point !== 'HASTA HQ Office') {
                    $commission += 2;
                }
            }
        }
        
        return $commission;
    }
}

