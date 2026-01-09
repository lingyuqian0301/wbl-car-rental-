<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;

class AdminInvoiceController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->get('search');
        $sort = $request->get('sort', 'invoice_no_asc');
        $dateFilterType = $request->get('date_filter_type', 'issue_date'); // issue_date or pickup_date
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $query = Booking::with([
            'customer.user',
            'vehicle', 
            'payments', 
            'invoice',
            'additionalCharges',
        ])->whereHas('invoice');

        // Search by plate number
        if ($search) {
            $query->whereHas('vehicle', function($vQuery) use ($search) {
                $vQuery->where('plate_number', 'like', "%{$search}%");
            });
        }

        // Date filter based on dropdown selection
        if ($dateFilterType === 'issue_date' && ($dateFrom || $dateTo)) {
            if ($dateFrom) {
                $query->whereHas('invoice', function($q) use ($dateFrom) {
                    $q->whereDate('issue_date', '>=', $dateFrom);
                });
            }
            if ($dateTo) {
                $query->whereHas('invoice', function($q) use ($dateTo) {
                    $q->whereDate('issue_date', '<=', $dateTo);
                });
            }
        } elseif ($dateFilterType === 'pickup_date' && ($dateFrom || $dateTo)) {
            if ($dateFrom) {
                $query->whereDate('rental_start_date', '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->whereDate('rental_start_date', '<=', $dateTo);
            }
        }

        // Join with invoice table for sorting
        $query->leftJoin('invoice', 'invoice.bookingID', '=', 'booking.bookingID')
              ->select('booking.*')
              ->distinct();

        // Sorting
        if ($sort === 'invoice_no_asc') {
            $query->orderBy('invoice.invoice_number', 'asc');
        } elseif ($sort === 'issue_date_desc') {
            $query->orderBy('invoice.issue_date', 'desc');
        } elseif ($sort === 'pickup_date_desc') {
            $query->orderBy('booking.rental_start_date', 'desc');
        } else {
            // Default: asc invoice no
            $query->orderBy('invoice.invoice_number', 'asc');
        }

        $bookings = $query->paginate(20)->withQueryString();

        // Summary stats for header
        $today = Carbon::today();
        $totalInvoices = Invoice::count();
        $totalBookings = Booking::whereHas('invoice')->count();
        
        $totalToday = Booking::whereHas('invoice')
            ->whereDate('rental_start_date', $today)
            ->count();

        return view('admin.invoices.index', [
            'bookings' => $bookings,
            'search' => $search,
            'sort' => $sort,
            'dateFilterType' => $dateFilterType,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'totalInvoices' => $totalInvoices,
            'totalBookings' => $totalBookings,
            'totalToday' => $totalToday,
            'today' => $today,
        ]);
    }
}