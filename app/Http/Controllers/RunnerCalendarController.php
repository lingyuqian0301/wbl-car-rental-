<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\RunnerNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class RunnerCalendarController extends Controller
{
    /**
     * Show the runner calendar
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $today = Carbon::today();
        $currentDate = $request->get('date', now()->format('Y-m-d'));
        $currentView = $request->get('view', 'month');

        // Get bookings assigned to this runner (staff_served = runner's userID)
        // where pickup or return is not at HASTA HQ Office
        $bookings = Booking::with(['vehicle', 'customer.user'])
            ->where('staff_served', $user->userID)
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
            ->get();

        // Group tasks by date for calendar display
        // Each pickup task = 1 task, each return task = 1 task
        $tasksByDate = [];
        foreach ($bookings as $booking) {
            $pickupDate = $booking->rental_start_date ? Carbon::parse($booking->rental_start_date)->format('Y-m-d') : null;
            $returnDate = $booking->rental_end_date ? Carbon::parse($booking->rental_end_date)->format('Y-m-d') : null;
            $pickupLocation = $booking->pickup_point ?? null;
            $returnLocation = $booking->return_point ?? null;

            // Add pickup task if not at HASTA HQ Office
            if ($pickupDate && $pickupLocation && $pickupLocation !== 'HASTA HQ Office') {
                if (!isset($tasksByDate[$pickupDate])) {
                    $tasksByDate[$pickupDate] = [];
                }
                $tasksByDate[$pickupDate][] = [
                    'booking' => $booking,
                    'type' => 'pickup',
                    'date' => Carbon::parse($booking->rental_start_date),
                    'location' => $pickupLocation,
                    'is_done' => Carbon::parse($pickupDate)->lt($today),
                ];
            }

            // Add return task if not at HASTA HQ Office
            if ($returnDate && $returnLocation && $returnLocation !== 'HASTA HQ Office') {
                if (!isset($tasksByDate[$returnDate])) {
                    $tasksByDate[$returnDate] = [];
                }
                $tasksByDate[$returnDate][] = [
                    'booking' => $booking,
                    'type' => 'return',
                    'date' => Carbon::parse($booking->rental_end_date),
                    'location' => $returnLocation,
                    'is_done' => Carbon::parse($returnDate)->lt($today),
                ];
            }
        }

        // Get unread notification count
        $unreadCount = $this->getUnreadNotificationCount();

        return view('runner.calendar.index', [
            'user' => $user,
            'today' => $today,
            'currentDate' => $currentDate,
            'currentView' => $currentView,
            'tasksByDate' => $tasksByDate,
            'unreadCount' => $unreadCount,
        ]);
    }

    /**
     * Get unread notification count for the runner
     */
    public function getUnreadNotificationCount(): int
    {
        $user = Auth::user();
        
        try {
            return RunnerNotification::where('runner_user_id', $user->userID)
                ->where('is_read', false)
                ->count();
        } catch (\Exception $e) {
            // Table might not exist yet
            return 0;
        }
    }

    /**
     * Get unread count as JSON (for AJAX calls)
     */
    public function unreadCount(): JsonResponse
    {
        return response()->json([
            'count' => $this->getUnreadNotificationCount()
        ]);
    }

    /**
     * Get notifications for dropdown
     */
    public function dropdownList(): JsonResponse
    {
        $user = Auth::user();
        
        try {
            $notifications = RunnerNotification::where('runner_user_id', $user->userID)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function($notif) {
                    return [
                        'id' => $notif->id,
                        'type' => $notif->type,
                        'message' => $notif->message,
                        'booking_id' => $notif->booking_id,
                        'is_read' => $notif->is_read,
                        'created_at' => Carbon::parse($notif->created_at)->diffForHumans(),
                    ];
                });

            return response()->json(['notifications' => $notifications]);
        } catch (\Exception $e) {
            return response()->json(['notifications' => []]);
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, $notificationId): JsonResponse
    {
        try {
            $notification = RunnerNotification::where('id', $notificationId)
                ->where('runner_user_id', Auth::id())
                ->first();

            if ($notification) {
                $notification->update(['is_read' => true]);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(): JsonResponse
    {
        try {
            RunnerNotification::where('runner_user_id', Auth::id())
                ->where('is_read', false)
                ->update(['is_read' => true]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * All notifications page
     */
    public function allNotifications(): View
    {
        $user = Auth::user();
        
        try {
            $notifications = RunnerNotification::where('runner_user_id', $user->userID)
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        } catch (\Exception $e) {
            $notifications = collect();
        }

        return view('runner.notifications.index', [
            'user' => $user,
            'notifications' => $notifications,
        ]);
    }

    /**
     * Create notification when runner is assigned to a task
     * Call this from AdminRunnerTaskController when assigning a runner
     */
    public static function createTaskAssignedNotification(Booking $booking, $runnerUserId, $taskType = 'both'): void
    {
        try {
            $message = '';
            $pickupLocation = $booking->pickup_point ?? null;
            $returnLocation = $booking->return_point ?? null;
            $pickupDate = $booking->rental_start_date ? Carbon::parse($booking->rental_start_date)->format('d M Y H:i') : 'N/A';
            $returnDate = $booking->rental_end_date ? Carbon::parse($booking->rental_end_date)->format('d M Y H:i') : 'N/A';

            // Create pickup notification if needed
            if (($taskType === 'both' || $taskType === 'pickup') && $pickupLocation && $pickupLocation !== 'HASTA HQ Office') {
                RunnerNotification::create([
                    'runner_user_id' => $runnerUserId,
                    'booking_id' => $booking->bookingID,
                    'type' => 'new_pickup_task',
                    'message' => "New pickup task assigned: Booking #{$booking->bookingID} - Pickup at {$pickupLocation} on {$pickupDate}",
                    'is_read' => false,
                ]);
            }

            // Create return notification if needed
            if (($taskType === 'both' || $taskType === 'return') && $returnLocation && $returnLocation !== 'HASTA HQ Office') {
                RunnerNotification::create([
                    'runner_user_id' => $runnerUserId,
                    'booking_id' => $booking->bookingID,
                    'type' => 'new_return_task',
                    'message' => "New return task assigned: Booking #{$booking->bookingID} - Return at {$returnLocation} on {$returnDate}",
                    'is_read' => false,
                ]);
            }
        } catch (\Exception $e) {
            // Silently fail if table doesn't exist
            \Log::warning('Failed to create runner notification: ' . $e->getMessage());
        }
    }
}

