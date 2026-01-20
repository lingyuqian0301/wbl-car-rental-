<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
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
            // 'additionalCharges', // AdditionalCharges table doesn't exist in database
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
            // AdditionalCharges table doesn't exist in database
            // $additionalCharges = $booking->additionalCharges;
            $additionalChargesTotal = 0; // Set to 0 since table doesn't exist
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
            // 'additionalCharges', // AdditionalCharges table doesn't exist in database
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

    /**
     * Send invoice email to customer
     */
    public function sendEmail(Request $request, Booking $booking): JsonResponse
    {
        try {
            // Load booking with relations (same as InvoiceController)
            $booking->load([
                'customer.user', 
                'vehicle.car', 
                'vehicle.motorcycle', 
                'payments', 
                'invoice'
            ]);

            $customer = $booking->customer;
            $user = $customer ? $customer->user : null;
            
            if (!$user || !$user->email) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer email not found.',
                ], 404);
            }

            // Generate PDF invoice (using same logic as InvoiceController::generatePDF)
            $invoiceData = $booking->invoice;
            if (!$invoiceData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice not found for this booking.',
                ], 404);
            }

            // Get voucher (same as InvoiceController)
            $voucher = null;
            $discountAmount = 0;
            if ($customer) {
                $loyaltyCard = \Illuminate\Support\Facades\DB::table('loyaltycard')
                    ->where('customerID', $customer->customerID)
                    ->first();
                
                if ($loyaltyCard) {
                    $usedVoucher = \Illuminate\Support\Facades\DB::table('voucher')
                        ->where('loyaltyCardID', $loyaltyCard->loyaltyCardID)
                        ->where('voucher_isActive', 0)
                        ->orderBy('voucherID', 'desc')
                        ->first();
                    
                    if ($usedVoucher) {
                        $voucher = $usedVoucher;
                    }
                }
            }

            // Calculate totals (same as InvoiceController)
            $verifiedPayments = $booking->payments()
                ->where('payment_status', 'Verified')
                ->orderBy('payment_date', 'asc')
                ->get();

            $allPayments = $booking->payments()
                ->orderBy('payment_date', 'asc')
                ->get();

            $totalPaid = $verifiedPayments->sum('total_amount');

            $vehicle = $booking->vehicle;
            $dailyRate = $vehicle->rental_price ?? 0;
            $rentalBase = $dailyRate * ($booking->duration ?? 1);
            
            $addonPrices = ['power_bank' => 5, 'phone_holder' => 5, 'usb_wire' => 3];
            $addonNames = ['power_bank' => 'Power Bank', 'phone_holder' => 'Phone Holder', 'usb_wire' => 'USB Wire'];
            $addonsArray = !empty($booking->addOns_item) ? explode(',', $booking->addOns_item) : [];
            $addonsBreakdown = [];
            $addonsTotal = 0;
            
            foreach ($addonsArray as $addon) {
                if (isset($addonPrices[$addon])) {
                    $addonPrice = $addonPrices[$addon];
                    $addonTotal = $addonPrice * ($booking->duration ?? 1);
                    $addonsBreakdown[] = [
                        'name' => $addonNames[$addon],
                        'daily_price' => $addonPrice,
                        'duration' => $booking->duration ?? 1,
                        'total' => $addonTotal
                    ];
                    $addonsTotal += $addonTotal;
                }
            }

            $depositAmount = $booking->deposit_amount ?? 50;
            $pickupSurcharge = 0;
            $baseAmount = $rentalBase + $addonsTotal;
            
            if ($voucher && $voucher->discount_type === 'PERCENT') {
                $discountAmount = $baseAmount * ($voucher->discount_amount / 100);
            } elseif ($voucher && $voucher->discount_type === 'FLAT') {
                $discountAmount = min($voucher->discount_amount, $baseAmount);
            }
            
            $subtotalAfterDiscount = $baseAmount - $discountAmount;
            $finalTotal = $subtotalAfterDiscount + $depositAmount;
            $outstandingBalance = max(0, $finalTotal - $totalPaid);

            $localCustomer = \App\Models\Local::where('customerID', $customer->customerID)->first();
            $internationalCustomer = \App\Models\International::where('customerID', $customer->customerID)->first();
            $localstudent = $localCustomer ? ($customer->localStudent ?? null) : null;

            // Generate PDF with all necessary data
            $pdf = Pdf::loadView('pdf.invoice', [
                'booking' => $booking,
                'invoiceData' => $invoiceData,
                'customer' => $customer,
                'user' => $user,
                'vehicle' => $vehicle,
                'voucher' => $voucher,
                'discountAmount' => $discountAmount,
                'depositAmount' => $depositAmount,
                'rentalBase' => $rentalBase,
                'dailyRate' => $dailyRate,
                'addonsBreakdown' => $addonsBreakdown,
                'addonsTotal' => $addonsTotal,
                'pickupSurcharge' => $pickupSurcharge,
                'baseAmount' => $baseAmount,
                'subtotalAfterDiscount' => $subtotalAfterDiscount,
                'finalTotal' => $finalTotal,
                'totalPaid' => $totalPaid,
                'outstandingBalance' => $outstandingBalance,
                'allPayments' => $allPayments,
                'verifiedPayments' => $verifiedPayments,
                'localCustomer' => $localCustomer,
                'localstudent' => $localstudent,
                'internationalCustomer' => $internationalCustomer,
                'invoiceDate' => now(),
            ]);

            // Send email
            \Illuminate\Support\Facades\Mail::to($user->email)
                ->send(new \App\Mail\BookingInvoiceMail($booking, $pdf));

            return response()->json([
                'success' => true,
                'message' => 'Invoice email sent successfully to ' . $user->email,
            ]);
        } catch (\Exception $e) {
            \Log::error('Invoice Email Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send invoice email: ' . $e->getMessage(),
            ], 500);
        }
    }
}