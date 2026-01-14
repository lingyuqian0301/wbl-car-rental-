<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

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

    /**
     * Export invoices as PDF
     */
    public function exportPdf(Request $request)
    {
        $query = $this->buildInvoiceQuery($request);
        $bookings = $query->get();

        $pdf = Pdf::loadView('admin.invoices.export-pdf', [
            'bookings' => $bookings,
            'filters' => $request->all(),
        ]);

        return $pdf->download('invoices-export-' . date('Y-m-d') . '.pdf');
    }

    /**
     * Export invoices as Excel (CSV)
     */
    public function exportExcel(Request $request)
    {
        $query = $this->buildInvoiceQuery($request);
        $bookings = $query->get();

        $data = $bookings->map(function($booking) {
            $invoice = $booking->invoice;
            $customer = $booking->customer;
            $user = $customer->user ?? null;
            $vehicle = $booking->vehicle ?? null;
            $totalPaid = $booking->payments()->where('payment_status', 'Verified')->sum('total_amount');
            $depositAmount = $booking->deposit_amount ?? 0;
            $rentalAmount = $booking->rental_amount ?? 0;
            $additionalCharges = $booking->additionalCharges;
            $additionalChargesTotal = $additionalCharges ? ($additionalCharges->total_extra_charge ?? 0) : 0;
            $totalPaymentAmount = $depositAmount + $rentalAmount + $additionalChargesTotal;

            return [
                'Invoice ID' => $invoice->invoice_number ?? 'N/A',
                'Booking ID' => $booking->bookingID,
                'Customer Name' => $user->name ?? 'N/A',
                'Customer Email' => $user->email ?? 'N/A',
                'Vehicle Plate' => $vehicle->plate_number ?? 'N/A',
                'Issue Date' => $invoice->issue_date ? $invoice->issue_date->format('Y-m-d') : 'N/A',
                'Pickup Date' => $booking->rental_start_date ? \Carbon\Carbon::parse($booking->rental_start_date)->format('Y-m-d') : 'N/A',
                'Return Date' => $booking->rental_end_date ? \Carbon\Carbon::parse($booking->rental_end_date)->format('Y-m-d') : 'N/A',
                'Deposit Amount' => number_format($depositAmount, 2),
                'Rental Amount' => number_format($rentalAmount, 2),
                'Additional Charges' => number_format($additionalChargesTotal, 2),
                'Total Payment Amount' => number_format($totalPaymentAmount, 2),
                'Total Paid' => number_format($totalPaid, 2),
            ];
        });

        $filename = 'invoices-export-' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            if ($data->isNotEmpty()) {
                fputcsv($file, array_keys($data->first()));
            }
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Build invoice query with filters
     */
    private function buildInvoiceQuery(Request $request)
    {
        $search = $request->get('search');
        $sort = $request->get('sort', 'invoice_no_asc');
        $dateFilterType = $request->get('date_filter_type', 'issue_date');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $query = Booking::with([
            'customer.user',
            'vehicle', 
            'payments', 
            'invoice',
            'additionalCharges',
        ])->whereHas('invoice');

        if ($search) {
            $query->whereHas('vehicle', function($vQuery) use ($search) {
                $vQuery->where('plate_number', 'like', "%{$search}%");
            });
        }

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

        $query->leftJoin('invoice', 'invoice.bookingID', '=', 'booking.bookingID')
              ->select('booking.*')
              ->distinct();

        if ($sort === 'invoice_no_asc') {
            $query->orderBy('invoice.invoice_number', 'asc');
        } elseif ($sort === 'issue_date_desc') {
            $query->orderBy('invoice.issue_date', 'desc');
        } elseif ($sort === 'pickup_date_desc') {
            $query->orderBy('booking.rental_start_date', 'desc');
        } else {
            $query->orderBy('invoice.invoice_number', 'asc');
        }

        return $query;
    }
}