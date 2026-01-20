<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class AdminRunnerTaskController extends Controller
{
    public function index(Request $request): View
    {
        $today = Carbon::today();
        
        // Get bookings that need runner:
        // Show bookings where:
        // - Only pickup point != 'HASTA HQ Office' (return can be anything)
        // - OR only return point != 'HASTA HQ Office' (pickup can be anything)
        // - OR both pickup and return point != 'HASTA HQ Office'
        // This means: (pickup != 'HASTA HQ Office') OR (return != 'HASTA HQ Office')
        // Including NULL/empty values (they are not equal to 'HASTA HQ Office')
        // Show ALL bookings (past, current, future) - not just future ones
        $query = Booking::with(['customer.user', 'vehicle'])
            ->whereIn('booking_status', ['Pending', 'Confirmed'])
            ->where(function($q) {
                // Show if pickup_point is NOT 'HASTA HQ Office' (including NULL/empty)
                $q->where(function($subQ) {
                    $subQ->whereNull('pickup_point')
                         ->orWhere('pickup_point', '')
                         ->orWhere('pickup_point', '!=', 'HASTA HQ Office');
                })
                // OR return_point is NOT 'HASTA HQ Office' (including NULL/empty)
                ->orWhere(function($subQ) {
                    $subQ->whereNull('return_point')
                         ->orWhere('return_point', '')
                         ->orWhere('return_point', '!=', 'HASTA HQ Office');
                });
            });

        // Search by booking ID or customer name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('bookingID', 'like', "%{$search}%")
                  ->orWhereHas('customer.user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by booking status (done/current/upcoming)
        $filterBookingStatus = $request->get('filter_booking_status');
        if ($filterBookingStatus === 'done') {
            $query->where('rental_end_date', '<', $today);
        } elseif ($filterBookingStatus === 'current') {
            $query->where('rental_start_date', '<=', $today)
                  ->where('rental_end_date', '>=', $today);
        } elseif ($filterBookingStatus === 'upcoming') {
            $query->where('rental_start_date', '>', $today);
        }

        // Filter by assigned/unassigned
        $filterAssigned = $request->get('filter_assigned');
        if ($filterAssigned === 'assigned') {
            $query->whereNotNull('staff_served');
        } elseif ($filterAssigned === 'unassigned') {
            $query->whereNull('staff_served');
        }

        // Filter by runner
        $filterRunner = $request->get('filter_runner');
        if ($filterRunner) {
            $query->where('staff_served', $filterRunner);
        }

        // Sort functionality
        $sortBy = $request->get('sort', 'pickup_asc');
        if ($sortBy === 'booking_desc') {
            $query->orderBy('bookingID', 'desc');
        } elseif ($sortBy === 'pickup_desc') {
            $query->orderBy('rental_start_date', 'desc');
        } else {
            // Default: pickup_asc and unassigned first
            $query->orderByRaw('CASE WHEN staff_served IS NULL THEN 0 ELSE 1 END')
                  ->orderBy('rental_start_date', 'asc');
        }

        $bookings = $query->paginate(20)->withQueryString();

        // Summary stats for header - use same query logic as above
        // Show bookings where: (pickup != 'HASTA HQ Office') OR (return != 'HASTA HQ Office')
        // Including NULL/empty values
        $runnerTaskCondition = function($q) {
            $q->where(function($subQ) {
                $subQ->whereNull('pickup_point')
                     ->orWhere('pickup_point', '')
                     ->orWhere('pickup_point', '!=', 'HASTA HQ Office');
            })->orWhere(function($subQ) {
                $subQ->whereNull('return_point')
                     ->orWhere('return_point', '')
                     ->orWhere('return_point', '!=', 'HASTA HQ Office');
            });
        };
        
        $totalBookings = Booking::whereIn('booking_status', ['Pending', 'Confirmed'])
            ->where($runnerTaskCondition)
            ->count();
        
        $assignedCount = Booking::whereIn('booking_status', ['Pending', 'Confirmed'])
            ->whereNotNull('staff_served')
            ->where($runnerTaskCondition)
            ->count();
        
        $unassignedCount = $totalBookings - $assignedCount;

        // Get all active runners for filter dropdown
        $runners = User::where('isActive', true)
            ->whereHas('staff', function($q) {
                $q->whereHas('runner');
            })
            ->with(['staff.runner'])
            ->orderBy('name')
            ->get();

        return view('admin.runner-tasks.index', [
            'bookings' => $bookings,
            'search' => $request->get('search'),
            'filterBookingStatus' => $filterBookingStatus,
            'filterAssigned' => $filterAssigned,
            'filterRunner' => $filterRunner,
            'sort' => $sortBy,
            'runners' => $runners,
            'totalBookings' => $totalBookings,
            'assignedCount' => $assignedCount,
            'unassignedCount' => $unassignedCount,
            'today' => $today,
        ]);
    }

    public function updateRunner(Request $request, Booking $booking): JsonResponse
    {
        $request->validate([
            'runner_id' => 'nullable|exists:user,userID',
        ]);

        // Verify the user is a runner
        if ($request->runner_id) {
            $runner = User::where('userID', $request->runner_id)
                ->whereHas('staff', function($q) {
                    $q->whereHas('runner');
                })->first();

            if (!$runner) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected user is not a runner.',
                ], 422);
            }
        }

        // Get old runner ID to check if runner changed
        $oldRunnerId = $booking->staff_served;

        $booking->update([
            'staff_served' => $request->runner_id ?: null,
            'lastUpdateDate' => now(),
        ]);

        // Create notification for the new runner if a runner is assigned
        if ($request->runner_id && $request->runner_id != $oldRunnerId) {
            // Reload booking with relationships
            $booking->load(['vehicle', 'customer.user']);
            
            // Create notifications for pickup and return tasks
            RunnerCalendarController::createTaskAssignedNotification($booking, $request->runner_id, 'both');
        }

        return response()->json([
            'success' => true,
            'message' => $request->runner_id ? 'Runner assigned successfully.' : 'Runner unassigned successfully.',
        ]);
    }
}

