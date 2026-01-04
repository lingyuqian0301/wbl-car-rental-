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
        // Get only cancelled bookings
        $query = Booking::with(['user', 'payments', 'cancelledByUser', 'refundProcessedByUser'])
            ->where('booking_status', 'Cancelled');

        // Filter by date range if provided (use cancelled_at or updated_at as fallback)
        if ($request->filled('date_from')) {
            $query->where(function($q) use ($request) {
                $q->whereNotNull('cancelled_at')
                  ->whereDate('cancelled_at', '>=', $request->date_from)
                  ->orWhere(function($q2) use ($request) {
                      $q2->whereNull('cancelled_at')
                         ->whereDate('updated_at', '>=', $request->date_from);
                  });
            });
        }
        if ($request->filled('date_to')) {
            $query->where(function($q) use ($request) {
                $q->whereNotNull('cancelled_at')
                  ->whereDate('cancelled_at', '<=', $request->date_to)
                  ->orWhere(function($q2) use ($request) {
                      $q2->whereNull('cancelled_at')
                         ->whereDate('updated_at', '<=', $request->date_to);
                  });
            });
        }

        // Filter by refund status
        if ($request->filled('refund_status')) {
            $query->where('refund_status', $request->refund_status);
        }

        // Sort by cancellation date (latest first), fallback to updated_at
        $query->orderByRaw('COALESCE(cancelled_at, updated_at) DESC');

        $cancellations = $query->paginate(20)->withQueryString();

        // Summary stats for header
        $today = \Carbon\Carbon::today();
        $totalCancellations = Booking::where('booking_status', 'Cancelled')->count();
        $pendingRefunds = Booking::where('booking_status', 'Cancelled')
            ->where('refund_status', 'Pending')
            ->count();
        $completedRefunds = Booking::where('booking_status', 'Cancelled')
            ->where('refund_status', 'Completed')
            ->count();
        $cancellationsToday = Booking::where('booking_status', 'Cancelled')
            ->whereDate('updated_at', $today)
            ->count();

        $viewName = str_starts_with(Route::currentRouteName(), 'staff.') ? 'staff.cancellations.index' : 'admin.cancellations.index';
        return view($viewName, [
            'cancellations' => $cancellations,
            'dateFrom' => $request->get('date_from'),
            'dateTo' => $request->get('date_to'),
            'refundStatus' => $request->get('refund_status'),
            'totalCancellations' => $totalCancellations,
            'pendingRefunds' => $pendingRefunds,
            'completedRefunds' => $completedRefunds,
            'cancellationsToday' => $cancellationsToday,
            'today' => $today,
        ]);
    }

    public function updateCancellation(Request $request, Booking $booking): JsonResponse
    {
        $request->validate([
            'refund_status' => 'required|in:Pending,Processing,Completed,Rejected',
            'refund_reason' => 'required_if:refund_status,Rejected|nullable|string|max:1000',
        ]);

        $updateData = [
            'refund_status' => $request->refund_status,
            'refund_processed_by' => Auth::id(),
            'refund_processed_at' => now(),
        ];

        if ($request->filled('refund_reason')) {
            $updateData['refund_reason'] = $request->refund_reason;
        } elseif ($request->refund_status === 'Rejected') {
            return response()->json([
                'success' => false,
                'message' => 'Reason is required when status is Rejected.',
            ], 422);
        }

        $booking->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Cancellation status updated successfully.',
        ]);
    }
}






