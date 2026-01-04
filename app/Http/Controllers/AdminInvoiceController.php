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
        $sortBookingDate = $request->get('sort_booking_date', 'desc');
        $sortInvoiceId = $request->get('sort_invoice_id', 'desc');

        $query = Booking::with(['user', 'vehicle', 'payments', 'invoice']);

        // Sort by booking date
        if ($sortBookingDate === 'asc') {
            $query->orderBy('rental_start_date', 'asc');
        } else {
            $query->orderBy('rental_start_date', 'desc');
        }

        // Sort by invoice ID (if invoice exists)
        if ($sortInvoiceId === 'asc') {
            $query->orderByRaw('(SELECT invoiceID FROM invoice WHERE invoice.bookingID = booking.bookingID) ASC');
        } else {
            $query->orderByRaw('(SELECT invoiceID FROM invoice WHERE invoice.bookingID = booking.bookingID) DESC');
        }

        $bookings = $query->paginate(20)->withQueryString();

        // Summary stats for header
        $today = Carbon::today();
        $totalInvoices = Invoice::count();
        $totalBookings = Booking::count();
        $totalToday = Booking::whereDate('rental_start_date', $today)->count();

        return view('admin.invoices.index', [
            'bookings' => $bookings,
            'sortBookingDate' => $sortBookingDate,
            'sortInvoiceId' => $sortInvoiceId,
            'totalInvoices' => $totalInvoices,
            'totalBookings' => $totalBookings,
            'totalToday' => $totalToday,
            'today' => $today,
        ]);
    }
}

