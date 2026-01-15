<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RunnerTaskController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $today = Carbon::today();
        
        // Get filter parameters
        $filterStatus = $request->get('status', 'all'); // done, upcoming, all
        $filterMonth = $request->get('month', Carbon::now()->month);
        $filterYear = $request->get('year', Carbon::now()->year);
        
        // Base query - get bookings assigned to this runner
        // Show tasks where at least one location (pickup OR return) is NOT "HASTA HQ Office"
        // Hide only if BOTH pickup AND return are "HASTA HQ Office"
        $bookings = Booking::with(['vehicle', 'customer.user'])
            ->where('staff_served', $user->userID)
            ->where(function($q) {
                // Show if pickup_point is NOT 'HASTA HQ Office' (and not null/empty)
                $q->where(function($subQ) {
                    $subQ->whereNotNull('pickup_point')
                         ->where('pickup_point', '!=', '')
                         ->where('pickup_point', '!=', 'HASTA HQ Office');
                })
                // OR return_point is NOT 'HASTA HQ Office' (and not null/empty)
                ->orWhere(function($subQ) {
                    $subQ->whereNotNull('return_point')
                         ->where('return_point', '!=', '')
                         ->where('return_point', '!=', 'HASTA HQ Office');
                });
            })
            ->where(function($q) use ($filterMonth, $filterYear) {
                $q->where(function($dateQ) use ($filterMonth, $filterYear) {
                    $dateQ->whereMonth('rental_start_date', $filterMonth)
                          ->whereYear('rental_start_date', $filterYear);
                })->orWhere(function($dateQ) use ($filterMonth, $filterYear) {
                    $dateQ->whereMonth('rental_end_date', $filterMonth)
                          ->whereYear('rental_end_date', $filterYear);
                });
            })
            ->orderBy('rental_start_date', 'desc')
            ->get();
        
        // Build task list from bookings
        $tasks = collect();
        $num = 1;
        
        foreach ($bookings as $booking) {
            $pickupDate = $booking->rental_start_date ? Carbon::parse($booking->rental_start_date) : null;
            $returnDate = $booking->rental_end_date ? Carbon::parse($booking->rental_end_date) : null;
            $pickupLocation = $booking->pickup_point ?? null;
            $returnLocation = $booking->return_point ?? null;
            
            // Check if pickup needs runner (not at HASTA HQ Office)
            if (!empty($pickupLocation) && $pickupLocation !== 'HASTA HQ Office' && $pickupDate) {
                if ($pickupDate->month == $filterMonth && $pickupDate->year == $filterYear) {
                    $deliveryDate = $pickupDate->copy()->subDay(); // One day before pickup
                    $isDone = $pickupDate->lt($today);
                    
                    // Apply status filter
                    if ($filterStatus === 'all' || 
                        ($filterStatus === 'done' && $isDone) || 
                        ($filterStatus === 'upcoming' && !$isDone)) {
                        $tasks->push([
                            'num' => $num++,
                            'booking_id' => $booking->bookingID,
                            'task_type' => 'Pickup',
                            'delivery_date' => $deliveryDate,
                            'task_date' => $pickupDate,
                            'location' => $pickupLocation,
                            'plate_number' => $booking->vehicle->plate_number ?? 'N/A',
                            'customer_name' => $booking->customer->user->name ?? 'N/A',
                            'is_done' => $isDone,
                        ]);
                    }
                }
            }
            
            // Check if return needs runner (not at HASTA HQ Office)
            if (!empty($returnLocation) && $returnLocation !== 'HASTA HQ Office' && $returnDate) {
                if ($returnDate->month == $filterMonth && $returnDate->year == $filterYear) {
                    $deliveryDate = $returnDate->copy()->subDay(); // One day before return
                    $isDone = $returnDate->lt($today);
                    
                    // Apply status filter
                    if ($filterStatus === 'all' || 
                        ($filterStatus === 'done' && $isDone) || 
                        ($filterStatus === 'upcoming' && !$isDone)) {
                        $tasks->push([
                            'num' => $num++,
                            'booking_id' => $booking->bookingID,
                            'task_type' => 'Return',
                            'delivery_date' => $deliveryDate,
                            'task_date' => $returnDate,
                            'location' => $returnLocation,
                            'plate_number' => $booking->vehicle->plate_number ?? 'N/A',
                            'customer_name' => $booking->customer->user->name ?? 'N/A',
                            'is_done' => $isDone,
                        ]);
                    }
                }
            }
        }
        
        // Sort by task_date descending
        $tasks = $tasks->sortByDesc('task_date')->values();
        
        // Re-number after sorting
        $tasks = $tasks->map(function($task, $index) {
            $task['num'] = $index + 1;
            return $task;
        });
        
        // Calculate totals
        $totalTasks = $tasks->count();
        $doneTasks = $tasks->where('is_done', true)->count();
        $upcomingTasks = $tasks->where('is_done', false)->count();
        
        return view('runner.tasks.index', [
            'user' => $user,
            'tasks' => $tasks,
            'filterStatus' => $filterStatus,
            'filterMonth' => $filterMonth,
            'filterYear' => $filterYear,
            'totalTasks' => $totalTasks,
            'doneTasks' => $doneTasks,
            'upcomingTasks' => $upcomingTasks,
            'today' => $today,
        ]);
    }
}
