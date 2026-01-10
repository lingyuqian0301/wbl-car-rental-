<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class AdminCancellationController extends Controller
{
    public function index(Request $request): View
    {
        // Get bookings with cancellation-related statuses: request cancelling, refunding, and cancelled
        $query = Booking::with(['customer.user', 'vehicle', 'payments'])
            ->whereIn('booking_status', ['request cancelling', 'refunding', 'Cancelled', 'cancelled']);

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

        // Filter by refund status
        if ($request->filled('refund_status')) {
            // Handle refund_status=false (bookings that haven't been refunded)
            if ($request->refund_status === 'false' || $request->refund_status === false) {
                // Refund status false means no refund payment exists
                $query->whereDoesntHave('payments', function($q) {
                    $q->where('payment_status', 'Refunded');
                });
            } else {
                // Map refund_status filter to booking_status values
                $statusMap = [
                    'request' => 'request cancelling',
                    'refunding' => 'refunding',
                    'cancelled' => ['Cancelled', 'cancelled'],
                    'rejected' => ['Cancelled', 'cancelled'],
                ];
                
                if (isset($statusMap[$request->refund_status])) {
                    $statusValue = $statusMap[$request->refund_status];
                    if (is_array($statusValue)) {
                        $query->whereIn('booking_status', $statusValue);
                    } else {
                        $query->where('booking_status', $statusValue);
                    }
                }
            }
        }

        // Filter by handled by (staff_served)
        if ($request->filled('handled_by')) {
            if ($request->handled_by === 'unassigned') {
                $query->whereNull('staff_served');
            } else {
                $query->where('staff_served', $request->handled_by);
            }
        }

        // Sort functionality
        $sortBy = $request->get('sort_by', 'date_desc');
        switch ($sortBy) {
            case 'booking_asc':
                $query->orderBy('bookingID', 'ASC');
                break;
            case 'date_desc':
                $query->orderBy('lastUpdateDate', 'DESC');
                break;
            default:
                $query->orderBy('lastUpdateDate', 'DESC');
        }

        $cancellations = $query->paginate(20)->withQueryString();

        // Summary stats for header
        $today = \Carbon\Carbon::today();
        $cancellationStatuses = ['request cancelling', 'refunding', 'Cancelled', 'cancelled'];
        $totalCancellations = Booking::whereIn('booking_status', $cancellationStatuses)->count();
        $pendingRefunds = Booking::where('booking_status', 'request cancelling')->count();
        $completedRefunds = Booking::whereIn('booking_status', ['Cancelled', 'cancelled'])->count();
        $cancellationsToday = Booking::whereIn('booking_status', $cancellationStatuses)
            ->whereDate('lastUpdateDate', $today)
            ->count();

        // Get all staffit and admins for handled by dropdown (exclude runner)
        $staffUsers = \App\Models\User::where(function($query) {
            $query->whereHas('staff', function($q) {
                $q->whereDoesntHave('runner'); // Exclude runners
            })->orWhereHas('admin');
        })->with(['staff.runner', 'staff.staffIt', 'admin'])->orderBy('name')->get();

        $viewName = str_starts_with(Route::currentRouteName(), 'staff.') ? 'staff.cancellations.index' : 'admin.cancellations.index';
        return view($viewName, [
            'cancellations' => $cancellations,
            'search' => $request->get('search'),
            'sortBy' => $request->get('sort_by', 'date_desc'),
            'refundStatus' => $request->get('refund_status'),
            'handledBy' => $request->get('handled_by'),
            'staffUsers' => $staffUsers,
            'totalCancellations' => $totalCancellations,
            'pendingRefunds' => $pendingRefunds,
            'completedRefunds' => $completedRefunds,
            'cancellationsToday' => $cancellationsToday,
            'today' => $today,
        ]);
    }

    public function updateCancellation(Request $request, Booking $booking): JsonResponse
    {
        $updateData = [
            'lastUpdateDate' => now(),
        ];

        // Update refund status if provided
        if ($request->filled('refund_status')) {
            $request->validate([
                'refund_status' => 'required|in:request,refunding,cancelled,rejected',
                'refund_reason' => 'required_if:refund_status,rejected|nullable|string|max:1000',
            ]);

            // Map refund_status to booking_status
            $statusMap = [
                'request' => 'request cancelling',
                'refunding' => 'refunding',
                'cancelled' => 'Cancelled',
                'rejected' => 'Cancelled',
            ];

            $updateData['booking_status'] = $statusMap[$request->refund_status] ?? $booking->booking_status;

            if ($request->filled('refund_reason')) {
                // Store refund reason in a note or comment field if available
                // For now, we'll just update the status
            } elseif ($request->refund_status === 'rejected') {
                return response()->json([
                    'success' => false,
                    'message' => 'Reason is required when status is Rejected.',
                ], 422);
            }
        }

        // Update handled by (staff_served) if provided
        if ($request->has('handled_by')) {
            if ($request->handled_by) {
                $request->validate([
                    'handled_by' => 'exists:user,userID',
                ]);
                $updateData['staff_served'] = $request->handled_by;
            } else {
                $updateData['staff_served'] = null;
            }
        }

        $booking->update($updateData);

        // Create notification for cancellation update
        try {
            $vehicle = $booking->vehicle;
            $vehicleInfo = $vehicle ? ($vehicle->vehicle_brand . ' ' . $vehicle->vehicle_model . ' (' . $vehicle->plate_number . ')') : 'N/A';
            $customer = $booking->customer;
            
            \App\Models\AdminNotification::create([
                'type' => 'booking_cancelled',
                'notifiable_type' => 'admin',
                'notifiable_id' => null,
                'user_id' => $customer->userID ?? null,
                'booking_id' => $booking->bookingID,
                'payment_id' => null,
                'message' => "Booking cancellation updated: Booking #{$booking->bookingID} - {$vehicleInfo}",
                'data' => [
                    'booking_id' => $booking->bookingID,
                    'vehicle_info' => $vehicleInfo,
                    'customer_name' => $customer->user->name ?? 'Customer',
                    'status' => $updateData['booking_status'] ?? $booking->booking_status,
                ],
                'is_read' => false,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to create cancellation notification: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Cancellation updated successfully.',
        ]);
    }

    public function sendEmail(Request $request, Booking $booking): JsonResponse
    {
        try {
            $customer = $booking->customer->user;
            
            if (!$customer || !$customer->email) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer email not found.',
                ], 404);
            }

            // Create cancellation email
            \Illuminate\Support\Facades\Mail::to($customer->email)
                ->send(new \App\Mail\CancellationNotificationMail($booking));

            return response()->json([
                'success' => true,
                'message' => 'Email sent successfully to ' . $customer->email,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage(),
            ], 500);
        }
    }
}






