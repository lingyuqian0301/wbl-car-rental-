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
        $filterPlateNo = $request->get('filter_plate_no');
        $filterPickupDate = $request->get('filter_pickup_date');
        $filterReturnDate = $request->get('filter_return_date');
        $filterServedBy = $request->get('filter_served_by');
        $filterBookingStatus = $request->get('filter_booking_status');
        $filterPaymentStatus = $request->get('filter_payment_status');

        $query = Booking::with(['customer.user', 'customer.loyaltyCard', 'vehicle', 'payments', 'invoice']);

        // Search by booking ID, plate number, or customer name
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('bookingID', 'like', "%{$search}%")
                  ->orWhereHas('vehicle', function($vQuery) use ($search) {
                      $vQuery->where('plate_number', 'like', "%{$search}%");
                  })
                  ->orWhereHas('customer.user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by plate number
        if ($filterPlateNo) {
            $query->whereHas('vehicle', function($vQuery) use ($filterPlateNo) {
                $vQuery->where('plate_number', 'like', "%{$filterPlateNo}%");
            });
        }

        // Filter by pickup date (handle datetime)
        if ($filterPickupDate) {
            $query->whereDate('rental_start_date', $filterPickupDate);
        }

        // Filter by return date (handle datetime)
        if ($filterReturnDate) {
            $query->whereDate('rental_end_date', $filterReturnDate);
        }

        // Filter by served by (staff_served)
        if ($filterServedBy) {
            $query->where('staff_served', $filterServedBy);
        }

        // Filter by booking status
        if ($filterBookingStatus) {
            $query->where('booking_status', $filterBookingStatus);
        }

        // Filter by payment status
        if ($filterPaymentStatus) {
            if ($filterPaymentStatus === 'Full') {
                $query->whereHas('payments', function($pQuery) {
                    $pQuery->where('payment_status', 'Full')
                           ->orWhere('isPayment_complete', true);
                });
            } elseif ($filterPaymentStatus === 'Deposit') {
                $query->whereHas('payments', function($pQuery) {
                    $pQuery->where('isPayment_complete', false)
                           ->where('payment_status', '!=', 'Full');
                });
            } elseif ($filterPaymentStatus === 'Unpaid') {
                $query->whereDoesntHave('payments', function($pQuery) {
                    $pQuery->where('payment_status', 'Verified');
                });
            }
        }

        // Filter by pickup date range
        $filterPickupDateFrom = $request->get('filter_pickup_date_from');
        $filterPickupDateTo = $request->get('filter_pickup_date_to');
        if ($filterPickupDateFrom && $filterPickupDateTo) {
            $query->whereBetween('rental_start_date', [$filterPickupDateFrom, $filterPickupDateTo]);
        } elseif ($filterPickupDateFrom) {
            $query->where('rental_start_date', '>=', $filterPickupDateFrom);
        } elseif ($filterPickupDateTo) {
            $query->where('rental_start_date', '<=', $filterPickupDateTo);
        }

        // Sorting
        $sort = $request->get('sort');
        if ($sort === 'booking_date_desc') {
            $query->orderBy('lastUpdateDate', 'desc');
        } elseif ($sort === 'pickup_date_desc') {
            $query->orderBy('rental_start_date', 'desc');
        } elseif ($sort === 'status_priority') {
            // Sort by status priority: upcoming (future dates) first, then current (today), then done (past dates)
            $today = Carbon::today()->format('Y-m-d');
            $query->orderByRaw("
                CASE 
                    WHEN rental_start_date > '{$today}' THEN 1
                    WHEN rental_start_date = '{$today}' THEN 2
                    WHEN rental_start_date < '{$today}' THEN 3
                    ELSE 4
                END
            ")
            ->orderBy('rental_start_date', 'asc');
        } else {
            // Default sort by booking ID desc
            $query->orderBy('bookingID', 'desc');
        }

        $bookings = $query->paginate(20)->withQueryString();

        // Get unique values for filters
        $plateNumbers = \App\Models\Vehicle::distinct()->pluck('plate_number')->filter()->sort()->values();
        // Get users who are staffit or admins (exclude runner)
        $staffUsers = \App\Models\User::where(function($query) {
            $query->whereHas('staff', function($q) {
                $q->whereDoesntHave('runner'); // Exclude runners
            })->orWhereHas('admin');
        })->with(['staff.runner', 'staff.staffIt', 'admin'])->orderBy('name')->get();
        $bookingStatuses = ['Pending', 'Confirmed', 'Request Cancellation', 'Refunding', 'Cancelled', 'Done'];
        $paymentStatuses = ['Full', 'Deposit', 'Unpaid'];

        // Summary stats for header
        $today = Carbon::today();
        $totalBookings = Booking::count();
        $totalPending = Booking::where('booking_status', 'Pending')->count();
        $totalConfirmed = Booking::where('booking_status', 'Confirmed')->count();
        $totalToday = Booking::whereDate('rental_start_date', $today)->count();

        // Get active tab (default to bookings)
        $activeTab = $request->get('tab', 'bookings');

        // Initialize leasing data (will be populated only if leasing tab is active)
        // Use empty paginator instead of collection to avoid Collection->total() error
        $leasingBookings = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20, 1);
        $leasingStats = [
            'totalBookings' => 0,
            'totalRevenue' => 0,
            'totalPaid' => 0,
            'ongoingBookings' => 0,
        ];

        if ($activeTab === 'leasing') {
            $leasingQuery = Booking::with(['customer.user', 'vehicle.documents', 'payments', 'invoice'])
                ->where('booking_status', '!=', 'Cancelled')
                ->where(function($q) {
                    $q->where('duration', '>', 15)
                      ->orWhereRaw('DATEDIFF(rental_end_date, rental_start_date) > 15');
                });

            $statusFilter = $request->get('status', 'all');
            if ($statusFilter === 'past') {
                $leasingQuery->where('rental_end_date', '<', $today);
            } elseif ($statusFilter === 'ongoing') {
                $leasingQuery->where('rental_start_date', '<=', $today)
                      ->where('rental_end_date', '>=', $today);
            } elseif ($statusFilter === 'future') {
                $leasingQuery->where('rental_start_date', '>', $today);
            }

            $leasingBookings = $leasingQuery->orderBy('rental_start_date', 'desc')->paginate(20)->withQueryString();

            // Calculate stats
            $allLeasingBookings = Booking::where('booking_status', '!=', 'Cancelled')
                ->where(function($q) {
                    $q->where('duration', '>', 15)
                      ->orWhereRaw('DATEDIFF(rental_end_date, rental_start_date) > 15');
                })
                ->get();

            $leasingStats['totalBookings'] = $allLeasingBookings->count();
            $leasingStats['totalRevenue'] = $allLeasingBookings->sum(function($booking) {
                return ($booking->deposit_amount ?? 0) + ($booking->rental_amount ?? 0);
            });
            $leasingStats['totalPaid'] = $allLeasingBookings->sum(function($booking) {
                return $booking->payments()->where('payment_status', 'Verified')->sum('total_amount');
            });
            $leasingStats['ongoingBookings'] = $allLeasingBookings->filter(function($booking) use ($today) {
                return $booking->rental_start_date <= $today && $booking->rental_end_date >= $today;
            })->count();
        }

        return view('admin.reservations.index', [
            'bookings' => $bookings,
            'leasingBookings' => $leasingBookings,
            'leasingStats' => $leasingStats,
            'activeTab' => $activeTab,
            'search' => $search,
            'filterPlateNo' => $filterPlateNo,
            'filterPickupDate' => $filterPickupDate,
            'filterReturnDate' => $filterReturnDate,
            'filterServedBy' => $filterServedBy,
            'filterBookingStatus' => $filterBookingStatus,
            'filterPaymentStatus' => $filterPaymentStatus,
            'plateNumbers' => $plateNumbers,
            'staffUsers' => $staffUsers,
            'bookingStatuses' => $bookingStatuses,
            'paymentStatuses' => $paymentStatuses,
            'totalBookings' => $totalBookings,
            'totalPending' => $totalPending,
            'totalConfirmed' => $totalConfirmed,
            'totalToday' => $totalToday,
            'today' => $today,
            'sort' => $sort ?? null,
            'statusFilter' => $request->get('status', 'all'),
        ]);
    }

    public function updateBookingStatus(Request $request, Booking $booking)
    {
        $request->validate([
            'booking_status' => 'nullable|string',
            'staff_served' => 'nullable|integer',
        ]);

        $updateData = [
            'lastUpdateDate' => Carbon::now(),
        ];

        if ($request->has('booking_status')) {
            $updateData['booking_status'] = $request->booking_status;
        }

        if ($request->has('staff_served')) {
            $updateData['staff_served'] = $request->staff_served ?: null;
        }

        $booking->update($updateData);

        // if ($request->booking_status === 'Confirmed') {
        //     \App\Models\Invoice::firstOrCreate(
        //         ['bookingID' => $booking->bookingID],
        //         [
        //             'invoice_number' => 'INV-' . date('Ymd') . '-' . $booking->bookingID,
        //             'issue_date'     => now(),
        //             'totalAmount'    => $booking->total_amount,
        //         ]
        //     );
        // }

        return response()->json([
            'success' => true,
            'message' => $request->has('staff_served') ? 'Served by updated successfully.' : 'Booking status updated successfully.',
        ]);
    }

    /**
     * Show booking detail page with tabs
     */
    public function show(Booking $booking): View
    {
        $booking->load([
            'customer.user',
            'customer.local',
            'customer.international',
            'customer.localStudent.studentDetails',
            'customer.internationalStudent.studentDetails',
            'customer.localUtmStaff.staffDetails',
            'customer.internationalUtmStaff.staffDetails',
            'customer.bookings',
            'customer.loyaltyCard',
            'vehicle.car',
            'vehicle.motorcycle',
            'vehicle.owner.personDetails',
            'vehicle.documents',
            'payments',
            'invoice',
            // 'review', // Review table has been removed from the database
            // 'additionalCharges', // AdditionalCharges table doesn't exist in database
            'vehicleConditionForms.images', // Load vehicle condition forms with images
        ]);

        // Calculate payment totals
        $totalPaid = $booking->payments()->where('payment_status', 'Verified')->sum('total_amount');
        $totalRequired = ($booking->deposit_amount ?? 0) + ($booking->rental_amount ?? 0);
        $outstandingBalance = max(0, $totalRequired - $totalPaid);
        
        // Get staff served info
        $staffServed = $booking->staff_served ? \App\Models\User::find($booking->staff_served) : null;
        
        // Get verify by users for payments
        $verifyByUsers = \App\Models\User::where(function($query) {
            $query->whereHas('staff')->orWhereHas('admin');
        })->orderBy('name')->get();

        // Get all transactions/payments for transaction detail tab
        $transactions = $booking->payments()->orderBy('payment_date', 'desc')->get();

        // Get active tab from query parameter - default to 'booking-detail'
        $activeTab = request()->get('tab', 'booking-detail');

        // Get all staff (admin and staff IT) for Served By dropdown - exclude runners
        $staffUsers = \App\Models\User::where(function($query) {
            $query->whereHas('staff', function($q) {
                $q->whereDoesntHave('runner'); // Exclude runners
            })->orWhereHas('admin');
        })->where('isActive', true)->orderBy('name')->get();

        // Get all runners for Runner Assigned dropdown
        $runners = \App\Models\User::whereHas('staff.runner')
            ->where('isActive', true)
            ->orderBy('name')
            ->get();

        // Get runner assigned info
        $runnerAssigned = $booking->staff_served ? \App\Models\User::find($booking->staff_served) : null;
        // Check if runnerAssigned is actually a runner
        if ($runnerAssigned && !$runnerAssigned->isRunner()) {
            $runnerAssigned = null; // Not a runner, so clear it
        }

        return view('admin.reservations.show', [
            'booking' => $booking,
            'totalPaid' => $totalPaid,
            'totalRequired' => $totalRequired,
            'outstandingBalance' => $outstandingBalance,
            'staffServed' => $staffServed,
            'verifyByUsers' => $verifyByUsers,
            'transactions' => $transactions,
            'activeTab' => $activeTab,
            'staffUsers' => $staffUsers,
            'runners' => $runners,
            'runnerAssigned' => $runnerAssigned,
        ]);
    }

    /**
     * Update runner assigned to booking
     */
    public function updateRunner(Request $request, Booking $booking)
    {
        $request->validate([
            'runner_id' => 'nullable|integer',
        ]);

        $booking->update([
            'staff_served' => $request->runner_id ?: null,
            'lastUpdateDate' => Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Runner assignment updated successfully.',
        ]);
    }

}
