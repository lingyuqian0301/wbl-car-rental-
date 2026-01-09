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
        try {
            // Check if notification table exists
            if (!\Illuminate\Support\Facades\Schema::hasTable('notification')) {
                $viewName = str_starts_with(Route::currentRouteName(), 'staff.') ? 'staff.notifications.index' : 'admin.notifications.index';
                return view($viewName, [
                    'notifications' => collect([]),
                    'unreadCount' => 0,
                ]);
            }
            
            $limit = $request->get('limit');
            
            $query = AdminNotification::where(function($query) {
                    $query->where('notifiable_type', 'admin')
                          ->orWhere(function($q) {
                              $q->where('notifiable_type', 'user')
                                ->where('notifiable_id', Auth::id());
                          });
                })
                ->with(['user', 'booking.customer.user', 'payment'])
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
        } catch (\Exception $e) {
            // If table doesn't exist or any error, return empty view
            $viewName = str_starts_with(Route::currentRouteName(), 'staff.') ? 'staff.notifications.index' : 'admin.notifications.index';
            return view($viewName, [
                'notifications' => collect([]),
                'unreadCount' => 0,
            ]);
        }
    }

    public function markAsRead(Request $request, AdminNotification $notification)
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('notification')) {
                return response()->json(['success' => false, 'message' => 'Notification table does not exist']);
            }
            
            $notification->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function markAllAsRead(Request $request)
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('notification')) {
                return response()->json(['success' => false, 'message' => 'Notification table does not exist']);
            }
            
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
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getUnreadCount()
    {
        try {
            // Check if notification table exists
            if (!\Illuminate\Support\Facades\Schema::hasTable('notification')) {
                return response()->json(['count' => 0]);
            }
            
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
        } catch (\Exception $e) {
            // If table doesn't exist or any error, return 0
            return response()->json(['count' => 0]);
        }
    }

    public function getDropdownList()
    {
        try {
            // Check if notification table exists
            if (!\Illuminate\Support\Facades\Schema::hasTable('notification')) {
                return response()->json(['notifications' => []]);
            }
            
            $notifications = AdminNotification::where(function($query) {
                    $query->where('notifiable_type', 'admin')
                          ->orWhere(function($q) {
                              $q->where('notifiable_type', 'user')
                                ->where('notifiable_id', Auth::id());
                          });
                })
                ->with(['user', 'booking.customer.user', 'payment'])
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
                            'id' => $notification->booking->bookingID ?? $notification->booking->id,
                            'vehicle' => $notification->booking->vehicle->full_model ?? null,
                            'user' => $notification->booking->customer && $notification->booking->customer->user ? $notification->booking->customer->user->name : null,
                        ] : null,
                        'payment' => $notification->payment ? [
                            'id' => $notification->payment->id,
                            'amount' => $notification->payment->amount,
                            'type' => $notification->payment->payment_type,
                        ] : null,
                    ];
                })
            ]);
        } catch (\Exception $e) {
            // If table doesn't exist or any error, return empty array
            return response()->json(['notifications' => []]);
        }
    }
}















