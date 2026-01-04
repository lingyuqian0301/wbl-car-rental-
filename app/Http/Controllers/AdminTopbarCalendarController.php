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
        // Note: 'vehicle' is not a relationship, it's a custom accessor, so we can't eager load it
        $bookingsQuery = Booking::with(['user', 'payments' => function($query) {
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

        // Check which bookings are unread by current user
        $unreadBookings = [];
        try {
            foreach ($bookings as $booking) {
                if (!$booking->isReadBy(Auth::id())) {
                    $unreadBookings[] = $booking->id;
                }
            }
        } catch (\Exception $e) {
            // If read status table doesn't exist yet, treat all as unread
            $unreadBookings = $bookings->pluck('id')->toArray();
        }

        // Group bookings by date for calendar display
        $bookingsByDate = [];
        foreach ($bookings as $booking) {
            $start = $booking->rental_start_date;
            $end = $booking->rental_end_date;
            
            $current = $start->copy();
            while ($current->lte($end)) {
                $dateKey = $current->format('Y-m-d');
                if (!isset($bookingsByDate[$dateKey])) {
                    $bookingsByDate[$dateKey] = [];
                }
                $bookingsByDate[$dateKey][] = $booking;
                $current->addDay();
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
        $staffAndAdmins = DB::table('user')
            ->whereIn('role', ['staff', 'admin'])
            ->orderBy('name')
            ->get();

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
            'staffAndAdmins' => $staffAndAdmins,
        ]);
    }

    public function markAsRead(Request $request, Booking $booking)
    {
        BookingReadStatus::updateOrCreate(
            [
                'bookingID' => $booking->id,
                'user_id' => Auth::id(),
            ],
            [
                'is_read' => true,
                'read_at' => now(),
            ]
        );

        return response()->json(['success' => true]);
    }

    public function markAsServed(Request $request, Booking $booking)
    {
        $request->validate([
            'served_by_user_id' => 'required|exists:user,id',
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
            $booking->load('user');
            Mail::to($booking->user->email)->send(new BalanceReminderMail($booking));
            return response()->json(['success' => true, 'message' => 'Balance reminder email sent successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to send email: ' . $e->getMessage()], 500);
        }
    }
}




