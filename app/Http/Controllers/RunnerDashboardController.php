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
        
        // Get runner's assigned bookings
        $bookings = $this->getRunnerTasksQuery($user->userID)->get();
        
        // Count tasks properly: 1 pickup = 1 task, 1 return = 1 task
        // If a booking has both pickup and return outside HQ = 2 tasks
        $totalTasks = 0;
        $upcomingTasks = 0;
        $doneTasks = 0;
        $todayTasksList = collect();
        
        foreach ($bookings as $booking) {
            $pickupDate = $booking->rental_start_date ? Carbon::parse($booking->rental_start_date) : null;
            $returnDate = $booking->rental_end_date ? Carbon::parse($booking->rental_end_date) : null;
            $pickupLocation = $booking->pickup_point ?? null;
            $returnLocation = $booking->return_point ?? null;
            
            // Count pickup task if location is not HASTA HQ Office
            if (!empty($pickupLocation) && $pickupLocation !== 'HASTA HQ Office' && $pickupDate) {
                $totalTasks++;
                if ($pickupDate->gt($today)) {
                    $upcomingTasks++;
                } elseif ($pickupDate->lt($today)) {
                    $doneTasks++;
                }
                // Add to today's tasks if pickup is today
                if ($pickupDate->isToday()) {
                    $todayTasksList->push([
                        'booking' => $booking,
                        'type' => 'pickup',
                        'location' => $pickupLocation,
                    ]);
                }
            }
            
            // Count return task if location is not HASTA HQ Office
            if (!empty($returnLocation) && $returnLocation !== 'HASTA HQ Office' && $returnDate) {
                $totalTasks++;
                if ($returnDate->gt($today)) {
                    $upcomingTasks++;
                } elseif ($returnDate->lt($today)) {
                    $doneTasks++;
                }
                // Add to today's tasks if return is today
                if ($returnDate->isToday()) {
                    $todayTasksList->push([
                        'booking' => $booking,
                        'type' => 'return',
                        'location' => $returnLocation,
                    ]);
                }
            }
        }
        
        return view('runner.dashboard', [
            'user' => $user,
            'today' => $today,
            'totalTasks' => $totalTasks,
            'upcomingTasks' => $upcomingTasks,
            'doneTasks' => $doneTasks,
            'todayTasks' => $todayTasksList,
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
}
