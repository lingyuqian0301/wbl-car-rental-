<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Car;
use App\Models\Motorcycle;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;

class AdminReservationController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->get('search');
        $filterBrand = $request->get('filter_brand');
        $filterModel = $request->get('filter_model');
        $filterPlateNo = $request->get('filter_plate_no');
        $filterPickupDate = $request->get('filter_pickup_date');
        $filterReturnDate = $request->get('filter_return_date');
        $filterDuration = $request->get('filter_duration');
        $filterServedBy = $request->get('filter_served_by');
        $filterBookingStatus = $request->get('filter_booking_status');

        $query = Booking::with(['customer.user', 'vehicle', 'payments', 'invoice']);

        // Search by customer name or booking ID
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('bookingID', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($customerQuery) use ($search) {
                      $customerQuery->whereHas('user', function($userQuery) use ($search) {
                          $userQuery->where('name', 'like', "%{$search}%");
                      });
                  });
            });
        }

        // Filter by vehicle brand
        if ($filterBrand) {
            $query->whereHas('vehicle', function($vQuery) use ($filterBrand) {
                $vQuery->where('vehicle_brand', $filterBrand);
            });
        }

        // Filter by vehicle model
        if ($filterModel) {
            $query->whereHas('vehicle', function($vQuery) use ($filterModel) {
                $vQuery->where('vehicle_model', $filterModel);
            });
        }

        // Filter by plate number
        if ($filterPlateNo) {
            $query->whereHas('vehicle', function($vQuery) use ($filterPlateNo) {
                $vQuery->where('plate_number', 'like', "%{$filterPlateNo}%");
            });
        }

        // Filter by pickup date
        if ($filterPickupDate) {
            $query->whereDate('rental_start_date', $filterPickupDate);
        }

        // Filter by return date
        if ($filterReturnDate) {
            $query->whereDate('rental_end_date', $filterReturnDate);
        }

        // Filter by duration
        if ($filterDuration) {
            $query->where('duration', $filterDuration);
        }

        // Filter by served by (staff_served)
        if ($filterServedBy) {
            $query->where('staff_served', $filterServedBy);
        }

        // Filter by booking status
        if ($filterBookingStatus) {
            $query->where('booking_status', $filterBookingStatus);
        }

        // Default sort by booking ID desc
        $query->orderBy('bookingID', 'desc');

        $bookings = $query->paginate(20)->withQueryString();

        // Get unique values for filters
        $brands = \App\Models\Vehicle::distinct()->pluck('vehicle_brand')->filter()->sort()->values();
        $models = \App\Models\Vehicle::distinct()->pluck('vehicle_model')->filter()->sort()->values();
        $plateNumbers = \App\Models\Vehicle::distinct()->pluck('plate_number')->filter()->sort()->values();
        $durations = Booking::distinct()->pluck('duration')->filter()->sort()->values();
        $staffUsers = \App\Models\User::where('role', 'staff')->orWhere('role', 'admin')->get();
        $bookingStatuses = ['Pending', 'Confirmed', 'Request Cancellation', 'Refunding', 'Cancelled', 'Done'];

        // Summary stats for header
        $today = Carbon::today();
        $totalBookings = Booking::count();
        $totalPending = Booking::where('booking_status', 'Pending')->count();
        $totalConfirmed = Booking::where('booking_status', 'Confirmed')->count();
        $totalToday = Booking::whereDate('rental_start_date', $today)->count();

        return view('admin.reservations.index', [
            'bookings' => $bookings,
            'search' => $search,
            'filterBrand' => $filterBrand,
            'filterModel' => $filterModel,
            'filterPlateNo' => $filterPlateNo,
            'filterPickupDate' => $filterPickupDate,
            'filterReturnDate' => $filterReturnDate,
            'filterDuration' => $filterDuration,
            'filterServedBy' => $filterServedBy,
            'filterBookingStatus' => $filterBookingStatus,
            'brands' => $brands,
            'models' => $models,
            'plateNumbers' => $plateNumbers,
            'durations' => $durations,
            'staffUsers' => $staffUsers,
            'bookingStatuses' => $bookingStatuses,
            'totalBookings' => $totalBookings,
            'totalPending' => $totalPending,
            'totalConfirmed' => $totalConfirmed,
            'totalToday' => $totalToday,
            'today' => $today,
        ]);
    }

    public function updateBookingStatus(Request $request, Booking $booking)
    {
        $request->validate([
            'booking_status' => 'required|string',
            'staff_served' => 'nullable|integer',
        ]);

        $booking->update([
            'booking_status' => $request->booking_status,
            'staff_served' => $request->staff_served ?? $booking->staff_served,
            'lastUpdateDate' => Carbon::now(),
        ]);

        if ($request->booking_status === 'Confirmed') {
            \App\Models\Invoice::firstOrCreate(
                ['bookingID' => $booking->bookingID],
                [
                    'invoice_number' => 'INV-' . date('Ymd') . '-' . $booking->bookingID,
                    'issue_date'     => now(),
                    'totalAmount'    => $booking->total_amount,
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Booking status updated successfully.',
        ]);
    }

}
