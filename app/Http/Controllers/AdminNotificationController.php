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
            
            // Check for upcoming bookings without full payment (pickup date in 3 days)
            $this->checkUpcomingBookingsWithoutFullPayment();
            
            $limit = $request->get('limit');
            
            // Get current user ID - use userID instead of Auth::id()
            $userId = auth()->check() ? auth()->user()->userID : null;
            
            $query = AdminNotification::where(function($query) use ($userId) {
                    $query->where('notifiable_type', 'admin')
                          ->orWhere(function($q) use ($userId) {
                              if ($userId) {
                                  $q->where('notifiable_type', 'user')
                                    ->where('notifiable_id', $userId);
                              } else {
                                  // If no user, exclude user notifications
                                  $q->whereRaw('1 = 0');
                              }
                          });
                })
                ->with(['user', 'booking.customer.user', 'payment'])
                ->orderBy('created_at', 'desc');
                
            if ($limit && is_numeric($limit)) {
                $notifications = $query->limit((int)$limit)->get();
            } else {
                $notifications = $query->paginate(20);
            }

            // Get current user ID - use userID instead of Auth::id()
            $userId = auth()->check() ? auth()->user()->userID : null;
            
            $unreadCount = AdminNotification::where(function($query) use ($userId) {
                    $query->where('notifiable_type', 'admin')
                          ->orWhere(function($q) use ($userId) {
                              if ($userId) {
                                  $q->where('notifiable_type', 'user')
                                    ->where('notifiable_id', $userId);
                              } else {
                                  // If no user, exclude user notifications
                                  $q->whereRaw('1 = 0');
                              }
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

    /**
     * Check for bookings with pickup date in 3 days that don't have full payment
     * This should repeat from 3 days before until the day or customer settles payment
     */
    private function checkUpcomingBookingsWithoutFullPayment()
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('notification')) {
                return;
            }
            
            $today = \Carbon\Carbon::today();
            $threeDaysFromNow = $today->copy()->addDays(3);
            
            // Get bookings with pickup date between today and 3 days from now
            // Using whereDate for proper date comparison
            $upcomingBookings = Booking::with(['customer.user', 'vehicle', 'payments'])
                ->whereDate('rental_start_date', '>=', $today)
                ->whereDate('rental_start_date', '<=', $threeDaysFromNow)
                ->whereIn('booking_status', ['Pending', 'Confirmed'])
                ->get();
            
            foreach ($upcomingBookings as $booking) {
                // Calculate total required and total paid
                $totalRequired = ($booking->rental_amount ?? 0) + ($booking->deposit_amount ?? 0);
                $totalPaid = $booking->payments()
                    ->where('payment_status', 'Verified')
                    ->sum('total_amount');
                
                // Check if payment is not full
                if ($totalPaid < $totalRequired) {
                    $pickupDate = \Carbon\Carbon::parse($booking->rental_start_date);
                    $daysUntilPickup = $today->diffInDays($pickupDate);
                    
                    // Only create notification if pickup is within 3 days (from today until 3 days from now)
                    if ($daysUntilPickup >= 0 && $daysUntilPickup <= 3) {
                        // Check if notification already exists for today (to avoid duplicates, but allow daily reminders)
                        $existingNotification = AdminNotification::where('booking_id', $booking->bookingID)
                            ->where('type', 'upcoming_booking_payment_incomplete')
                            ->whereDate('created_at', $today)
                            ->first();
                        
                        if (!$existingNotification) {
                            $vehicle = $booking->vehicle;
                            $vehicleInfo = $vehicle ? ($vehicle->vehicle_brand . ' ' . $vehicle->vehicle_model . ' (' . ($vehicle->plate_number ?? 'N/A') . ')') : 'N/A';
                            $customer = $booking->customer;
                            $outstandingAmount = $totalRequired - $totalPaid;
                            
                            AdminNotification::create([
                                'type' => 'upcoming_booking_payment_incomplete',
                                'notifiable_type' => 'admin',
                                'notifiable_id' => null,
                                'user_id' => $customer->userID ?? null,
                                'booking_id' => $booking->bookingID,
                                'payment_id' => null,
                                'message' => "Booking #{$booking->bookingID} - {$vehicleInfo} pickup in {$daysUntilPickup} day(s). Outstanding: RM " . number_format($outstandingAmount, 2),
                                'data' => [
                                    'booking_id' => $booking->bookingID,
                                    'vehicle_info' => $vehicleInfo,
                                    'customer_name' => $customer->user->name ?? 'Customer',
                                    'pickup_date' => $booking->rental_start_date,
                                    'days_until_pickup' => $daysUntilPickup,
                                    'outstanding_amount' => $outstandingAmount,
                                    'total_required' => $totalRequired,
                                    'total_paid' => $totalPaid,
                                ],
                                'is_read' => false,
                            ]);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to check upcoming bookings without full payment: ' . $e->getMessage());
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
            
            // Get current user ID - use userID instead of Auth::id()
            $userId = auth()->check() ? auth()->user()->userID : null;
            
            AdminNotification::where(function($query) use ($userId) {
                    $query->where('notifiable_type', 'admin')
                          ->orWhere(function($q) use ($userId) {
                              if ($userId) {
                                  $q->where('notifiable_type', 'user')
                                    ->where('notifiable_id', $userId);
                              } else {
                                  // If no user, exclude user notifications
                                  $q->whereRaw('1 = 0');
                              }
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
            
            // Get current user ID - use userID instead of Auth::id()
            $userId = auth()->check() ? auth()->user()->userID : null;
            
            $count = AdminNotification::where(function($query) use ($userId) {
                    $query->where('notifiable_type', 'admin')
                          ->orWhere(function($q) use ($userId) {
                              if ($userId) {
                                  $q->where('notifiable_type', 'user')
                                    ->where('notifiable_id', $userId);
                              } else {
                                  // If no user, exclude user notifications
                                  $q->whereRaw('1 = 0');
                              }
                          });
                })
                ->where('is_read', false)
                ->count();

            return response()->json(['count' => $count]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to get unread notification count: ' . $e->getMessage());
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
            
            // Get current user ID - use userID instead of Auth::id()
            $userId = auth()->check() ? auth()->user()->userID : null;
            
            $notifications = AdminNotification::where(function($query) use ($userId) {
                    $query->where('notifiable_type', 'admin')
                          ->orWhere(function($q) use ($userId) {
                              if ($userId) {
                                  $q->where('notifiable_type', 'user')
                                    ->where('notifiable_id', $userId);
                              } else {
                                  // If no user, exclude user notifications
                                  $q->whereRaw('1 = 0');
                              }
                          });
                })
                ->with(['user', 'booking.customer.user', 'payment'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            return response()->json([
                'notifications' => $notifications->map(function($notification) {
                    $bookingData = null;
                    if ($notification->booking) {
                        $vehicle = $notification->booking->vehicle;
                        $vehicleInfo = $vehicle ? ($vehicle->vehicle_brand . ' ' . $vehicle->vehicle_model . ' (' . ($vehicle->plate_number ?? 'N/A') . ')') : 'N/A';
                        $bookingData = [
                            'id' => $notification->booking->bookingID ?? $notification->booking->id,
                            'vehicle' => $vehicleInfo,
                            'user' => $notification->booking->customer && $notification->booking->customer->user ? $notification->booking->customer->user->name : null,
                        ];
                    }
                    
                    $paymentData = null;
                    if ($notification->payment) {
                        $paymentData = [
                            'id' => $notification->payment->paymentID ?? $notification->payment->id,
                            'amount' => $notification->payment->total_amount ?? $notification->payment->amount ?? 0,
                        ];
                    }
                    
                    return [
                        'id' => $notification->id,
                        'type' => $notification->type,
                        'message' => $notification->message,
                        'is_read' => $notification->is_read,
                        'created_at' => $notification->created_at->diffForHumans(),
                        'booking' => $bookingData,
                        'payment' => $paymentData,
                    ];
                })
            ]);
        } catch (\Exception $e) {
            // If table doesn't exist or any error, return empty array
            return response()->json(['notifications' => []]);
        }
    }
}















