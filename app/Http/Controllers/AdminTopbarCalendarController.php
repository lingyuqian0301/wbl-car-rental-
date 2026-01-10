<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingReadStatus;
use App\Models\BookingServedBy;
use App\Models\Car;
use App\Models\Motorcycle;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;
use App\Mail\BalanceReminderMail;

class AdminTopbarCalendarController extends Controller
{
    public function index(Request $request): View
    {
        $view = $request->get('view', 'month'); // month, week, day
        $vehicleId = $request->get('vehicle_id', 'all');
        $currentDate = $request->get('date', now()->format('Y-m-d'));

        // Get bookings based on filters
        $bookingsQuery = Booking::with(['customer.user', 'vehicle', 'payments' => function($query) {
                // Use payment_date instead of created_at for ordering
                $query->orderBy('payment_date', 'desc');
            }, 'confirmedByUser', 'completedByUser'])
            // Removed servedBy eager loading as booking_served_by table doesn't exist
            // Use confirmed_by field instead
            ->where('booking_status', '!=', 'Cancelled');

        if ($vehicleId !== 'all') {
            // Handle car_ and motorcycle_ prefixes
            if (str_starts_with($vehicleId, 'car_')) {
                $carId = str_replace('car_', '', $vehicleId);
                $bookingsQuery->where('vehicleID', $carId);
            } elseif (str_starts_with($vehicleId, 'motorcycle_')) {
                $motorcycleId = str_replace('motorcycle_', '', $vehicleId);
                $bookingsQuery->where('vehicleID', $motorcycleId);
            } else {
                $bookingsQuery->where('vehicleID', $vehicleId);
            }
        }

        $bookings = $bookingsQuery->get();

        // Check which bookings are unread by current user (separate for pickup and return)
        $unreadBookings = []; // For backward compatibility (any unread)
        $unreadPickups = []; // Pickup dates that are unread
        $unreadReturns = []; // Return dates that are unread
        try {
            foreach ($bookings as $booking) {
                $isPickupUnread = !$booking->isPickupReadBy(Auth::id());
                $isReturnUnread = !$booking->isReturnReadBy(Auth::id());
                
                if ($isPickupUnread) {
                    $unreadPickups[] = $booking->bookingID;
                }
                if ($isReturnUnread) {
                    $unreadReturns[] = $booking->bookingID;
                }
                // For overall count, booking is unread if either pickup or return is unread
                if ($isPickupUnread || $isReturnUnread) {
                    $unreadBookings[] = $booking->bookingID;
                }
            }
        } catch (\Exception $e) {
            // If read status table doesn't exist yet, treat all as unread
            $unreadBookings = $bookings->pluck('bookingID')->toArray();
            $unreadPickups = $bookings->pluck('bookingID')->toArray();
            $unreadReturns = $bookings->pluck('bookingID')->toArray();
        }

        // Group bookings by date for calendar display
        $bookingsByDate = [];
        foreach ($bookings as $booking) {
            try {
                $start = $booking->rental_start_date ? \Carbon\Carbon::parse($booking->rental_start_date) : null;
                $end = $booking->rental_end_date ? \Carbon\Carbon::parse($booking->rental_end_date) : null;
                
                if (!$start || !$end) continue;
                
                $current = $start->copy()->startOfDay();
                $endDay = $end->copy()->startOfDay();
                
                while ($current->lte($endDay)) {
                    $dateKey = $current->format('Y-m-d');
                    if (!isset($bookingsByDate[$dateKey])) {
                        $bookingsByDate[$dateKey] = [];
                    }
                    // Avoid duplicate bookings in the same date
                    $bookingExists = false;
                    foreach ($bookingsByDate[$dateKey] as $existingBooking) {
                        if ($existingBooking->bookingID === $booking->bookingID) {
                            $bookingExists = true;
                            break;
                        }
                    }
                    if (!$bookingExists) {
                        $bookingsByDate[$dateKey][] = $booking;
                    }
                    $current->addDay();
                }
            } catch (\Exception $e) {
                // Skip bookings with invalid dates
                continue;
            }
        }

        // Get all vehicles for filter - join with vehicle table
        $cars = Car::with('vehicle')
            ->join('vehicle', 'car.vehicleID', '=', 'vehicle.vehicleID')
            ->select('car.*', 'vehicle.vehicle_brand', 'vehicle.vehicle_model', 'vehicle.plate_number')
            ->orderBy('vehicle.vehicle_brand')
            ->orderBy('vehicle.vehicle_model')
            ->get();
        $motorcycles = Motorcycle::with('vehicle')
            ->join('vehicle', 'motorcycle.vehicleID', '=', 'vehicle.vehicleID')
            ->select('motorcycle.*', 'vehicle.vehicle_brand', 'vehicle.vehicle_model', 'vehicle.plate_number')
            ->orderBy('vehicle.vehicle_brand')
            ->orderBy('vehicle.vehicle_model')
            ->get();

        // Get all staff and admins for tick colors
      // NEW CODE (FIXED)
        // Since 'role' column doesn't exist, we fetch Admins and Staff separately and merge them.
        
        $admins = DB::table('user')
            ->join('admin', 'user.userID', '=', 'admin.userID')
            ->select('user.*', DB::raw("'admin' as role")) // Manually add 'role' for the view
            ->get();

        $staff = DB::table('user')
            ->join('staff', 'user.userID', '=', 'staff.userID')
            ->select('user.*', DB::raw("'staff' as role")) // Manually add 'role' for the view
            ->get();

        // Merge both lists and sort by name
        $staffAndAdmins = $admins->merge($staff)->sortBy('name');
        $viewName = str_starts_with(Route::currentRouteName(), 'staff.') ? 'staff.topbar-calendar.index' : 'admin.topbar-calendar.index';
        return view($viewName, [
            'bookings' => $bookings,
            'bookingsByDate' => $bookingsByDate,
            'cars' => $cars,
            'motorcycles' => $motorcycles,
            'selectedVehicle' => $vehicleId,
            'currentView' => $view,
            'currentDate' => $currentDate,
            'unreadBookings' => $unreadBookings,
            'unreadPickups' => $unreadPickups,
            'unreadReturns' => $unreadReturns,
            'staffAndAdmins' => $staffAndAdmins,
        ]);
    }

    public function markAsRead(Request $request, Booking $booking)
    {
        try {
            // Get date_type from request (pickup or return), default to pickup
            $dateType = $request->input('date_type', 'pickup');
            
            // Validate date_type
            if (!in_array($dateType, ['pickup', 'return'])) {
                $dateType = 'pickup';
            }
            
            BookingReadStatus::updateOrCreate(
                [
                    'booking_id' => $booking->bookingID,
                    'user_id' => Auth::id(),
                    'date_type' => $dateType,
                ],
                [
                    'is_read' => true,
                    'read_at' => now(),
                ]
            );

            return response()->json(['success' => true, 'date_type' => $dateType]);
        } catch (\Exception $e) {
            // If table doesn't exist, still return success
            return response()->json(['success' => true, 'message' => 'Marked as read (table may not exist)']);
        }
    }

    public function markAsServed(Request $request, Booking $booking)
    {
        $request->validate([
            'served_by_user_id' => 'required|exists:user,userID',
            'notes' => 'nullable|string',
        ]);

        BookingServedBy::updateOrCreate(
            [
                'bookingID' => $booking->id,
                'served_by_user_id' => $request->served_by_user_id,
            ],
            [
                'served_at' => now(),
                'notes' => $request->notes,
            ]
        );

        return response()->json(['success' => true]);
    }

    public function confirmBooking(Request $request, Booking $booking)
    {
        $booking->update([
            'booking_status' => 'Confirmed',
            'confirmed_by' => Auth::id(),
            'confirmed_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Booking confirmed successfully.']);
    }

    public function completeBooking(Request $request, Booking $booking)
    {
        $booking->update([
            'booking_status' => 'Completed',
            'completed_by' => Auth::id(),
            'completed_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Booking completed successfully.']);
    }

    public function sendBalanceReminder(Request $request, Booking $booking)
    {
        try {
            $booking->load('customer.user');
            if ($booking->customer && $booking->customer->user) {
                Mail::to($booking->customer->user->email)->send(new BalanceReminderMail($booking));
            return response()->json(['success' => true, 'message' => 'Balance reminder email sent successfully.']);
            } else {
                return response()->json(['success' => false, 'message' => 'Customer or user not found for this booking.'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to send email: ' . $e->getMessage()], 500);
        }
    }
}




