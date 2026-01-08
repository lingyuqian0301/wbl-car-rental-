<?php

namespace App\Http\Controllers;

use App\Models\AdminNotification;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class AdminNotificationController extends Controller
{
    public function index(Request $request): View
    {
        $limit = $request->get('limit');
        
        $query = AdminNotification::where(function($query) {
                $query->where('notifiable_type', 'admin')
                      ->orWhere(function($q) {
                          $q->where('notifiable_type', 'user')
                            ->where('notifiable_id', Auth::id());
                      });
            })
            ->with(['user', 'booking.user', 'payment'])
            ->orderBy('created_at', 'desc');
            
        if ($limit && is_numeric($limit)) {
            $notifications = $query->limit((int)$limit)->get();
        } else {
            $notifications = $query->paginate(20);
        }

        $unreadCount = AdminNotification::where(function($query) {
                $query->where('notifiable_type', 'admin')
                      ->orWhere(function($q) {
                          $q->where('notifiable_type', 'user')
                            ->where('notifiable_id', Auth::id());
                      });
            })
            ->where('is_read', false)
            ->count();

        $viewName = str_starts_with(Route::currentRouteName(), 'staff.') ? 'staff.notifications.index' : 'admin.notifications.index';
        return view($viewName, [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }

    public function markAsRead(Request $request, AdminNotification $notification)
    {
        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function markAllAsRead(Request $request)
    {
        AdminNotification::where(function($query) {
                $query->where('notifiable_type', 'admin')
                      ->orWhere(function($q) {
                          $q->where('notifiable_type', 'user')
                            ->where('notifiable_id', Auth::id());
                      });
            })
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json(['success' => true]);
    }

    public function getUnreadCount()
    {
        $count = AdminNotification::where(function($query) {
                $query->where('notifiable_type', 'admin')
                      ->orWhere(function($q) {
                          $q->where('notifiable_type', 'user')
                            ->where('notifiable_id', Auth::id());
                      });
            })
            ->where('is_read', false)
            ->count();

        return response()->json(['count' => $count]);
    }

    public function getDropdownList()
    {
        $notifications = AdminNotification::where(function($query) {
                $query->where('notifiable_type', 'admin')
                      ->orWhere(function($q) {
                          $q->where('notifiable_type', 'user')
                            ->where('notifiable_id', Auth::id());
                      });
            })
            ->with(['user', 'booking.user', 'payment'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'notifications' => $notifications->map(function($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'message' => $notification->message,
                    'is_read' => $notification->is_read,
                    'created_at' => $notification->created_at->diffForHumans(),
                    'booking' => $notification->booking ? [
                        'id' => $notification->booking->id,
                        'vehicle' => $notification->booking->vehicle->full_model ?? null,
                        'user' => $notification->booking->user->name ?? null,
                    ] : null,
                    'payment' => $notification->payment ? [
                        'id' => $notification->payment->id,
                        'amount' => $notification->payment->amount,
                        'type' => $notification->payment->payment_type,
                    ] : null,
                ];
            })
        ]);
    }
}












