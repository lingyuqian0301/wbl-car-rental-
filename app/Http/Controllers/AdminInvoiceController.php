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
        $sortInvoiceNo = $request->get('sort_invoice_no', 'asc');
        $sortIssueDate = $request->get('sort_issue_date', 'desc');
        $issueDateFrom = $request->get('issue_date_from');
        $issueDateTo = $request->get('issue_date_to');
        $pickupDateFrom = $request->get('pickup_date_from');
        $pickupDateTo = $request->get('pickup_date_to');

        $query = Booking::with([
            'customer',        // 'customer.user' might be redundant if customer model has user details
            'vehicle',         // Simply 'vehicle' is usually enough
            'payments', 
            'invoice',
            // 'additionalCharges' // Remove if this relationship doesn't exist yet
        ])->whereHas('invoice'); // Only show bookings that HAVE an invoice

        // Filter by issue date range
        if ($issueDateFrom) {
            $query->whereHas('invoice', function($q) use ($issueDateFrom) {
                $q->whereDate('issue_date', '>=', $issueDateFrom);
            });
        }
        if ($issueDateTo) {
            $query->whereHas('invoice', function($q) use ($issueDateTo) {
                $q->whereDate('issue_date', '<=', $issueDateTo);
            });
        }

        // Filter by pickup date range (FIXED: Changed 'rental_start_date' to 'start_date')
        if ($pickupDateFrom) {
            $query->whereDate('start_date', '>=', $pickupDateFrom);
        }
        if ($pickupDateTo) {
            $query->whereDate('start_date', '<=', $pickupDateTo);
        }

        // Join with invoice table for sorting
        $query->leftJoin('invoice', 'invoice.bookingID', '=', 'booking.bookingID')
              ->select('booking.*')
              ->distinct();

        // Sort by invoice number
        if ($sortInvoiceNo === 'asc') {
            $query->orderBy('invoice.invoice_number', 'asc');
        } else {
            $query->orderBy('invoice.invoice_number', 'desc');
        }

        // Sort by issue date (secondary sort)
        if ($sortIssueDate === 'asc') {
            $query->orderBy('invoice.issue_date', 'asc');
        } else {
            $query->orderBy('invoice.issue_date', 'desc');
        }

        $bookings = $query->paginate(20)->withQueryString();

        // Summary stats for header
        $today = Carbon::today();
        $totalInvoices = Invoice::count();
        $totalBookings = Booking::whereHas('invoice')->count();
        
        // FIXED: Changed 'rental_start_date' to 'start_date' here too
        $totalToday = Booking::whereHas('invoice')
            ->whereDate('start_date', $today)
            ->count();

        return view('admin.invoices.index', [
            'bookings' => $bookings,
            'sortInvoiceNo' => $sortInvoiceNo,
            'sortIssueDate' => $sortIssueDate,
            'issueDateFrom' => $issueDateFrom,
            'issueDateTo' => $issueDateTo,
            'pickupDateFrom' => $pickupDateFrom,
            'pickupDateTo' => $pickupDateTo,
            'totalInvoices' => $totalInvoices,
            'totalBookings' => $totalBookings,
            'totalToday' => $totalToday,
            'today' => $today,
        ]);
    }
}