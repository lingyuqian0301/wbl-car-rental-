<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Invoice;
use App\Mail\BookingInvoiceMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\View\View;

class AdminPaymentController extends Controller
{
    /**
     * Display a listing of all payments.
     */
    public function index(): View
    {
        $payments = Payment::with(['booking.customer', 'booking.vehicle'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.payments.index', [
            'payments' => $payments,
        ]);
    }

    /**
     * Approve a payment, generate an invoice record, and send the Gmail.
     */
public function approve($id): RedirectResponse
{
    // 1. Find Payment & Update Status
    $payment = Payment::where('paymentID', $id)->firstOrFail();
    $payment->update(['status' => 'Verified', 'verified_by' => Auth::id()]);

    // 2. Update Booking Status
    $booking = $payment->booking;
    $booking->update(['booking_status' => 'Confirmed']);

    // 3. Create Invoice (Prevent Duplicate)
    $invoiceData = Invoice::firstOrCreate(
        ['bookingID' => $booking->bookingID],
        [
            'invoice_number' => 'INV-' . date('Ymd') . '-' . $booking->bookingID,
            'issue_date'     => now(),
            'totalAmount'    => $booking->total_price,
            'staffID'        => Auth::id(),
        ]
    );

    // 4. Clean Loyalty Logic (Stamps & Vouchers)
    try {
        $start = Carbon::parse($booking->pickup_date);
        $end   = Carbon::parse($booking->return_date);
        $hours = $start->diffInHours($end);

        // Rule: Minimum 9 hours to earn. 1 stamp per 3 hours.
        if ($hours >= 9) {
            $stamps = floor($hours / 3);

            // Update Loyalty Card (No schema guessing)
            DB::table('loyalty_cards')->updateOrInsert(
                ['customerID' => $booking->customerID],
                [
                    'total_stamps' => DB::raw("total_stamps + $stamps"),
                    'updated_at' => now()
                ]
            );

            // Check Milestone: 48 Stamps = 1 Free Day Voucher
            $currentStamps = DB::table('loyalty_cards')
                ->where('customerID', $booking->customerID)
                ->value('total_stamps');

            if ($currentStamps >= 48) {
                DB::table('vouchers')->insert([
                    'code'        => 'FREE-DAY-' . Str::upper(Str::random(6)),
                    'customerID'  => $booking->customerID,
                    'description' => '1 Free Day (Mon-Fri Only)',
                    'status'      => 'Active',
                    'created_at'  => now()
                ]);

                // Deduct stamps
                DB::table('loyalty_cards')
                    ->where('customerID', $booking->customerID)
                    ->decrement('total_stamps', 48);
            }
        }
    } catch (\Exception $e) {
        \Log::warning('Loyalty Logic Error: ' . $e->getMessage());
    }

    // 5. Generate PDF & Send Email
    // This MUST happen here so $pdf is not null
    $pdf = Pdf::loadView('pdf.invoice', compact('booking', 'invoiceData'));

    try {
        Mail::to($booking->customer->email)->send(new BookingInvoiceMail($booking, $pdf));
    } catch (\Exception $e) {
        return redirect()->route('admin.payments.index')
            ->with('error', 'Verified, but email failed: ' . $e->getMessage());
    }

    return redirect()->route('admin.payments.index')
        ->with('success', 'Payment verified and Invoice emailed.');
}
    // public function approve($id): RedirectResponse
    // {
    //     // 1. Find Payment by custom primary key 'paymentID'
    //     $payment = Payment::where('paymentID', $id)->firstOrFail();

    //     // 2. Update payment status and record the staff who verified it
    //     $payment->update([
    //         'status' => 'Verified',
    //         'verified_by' => Auth::id(),
    //         'rejected_reason' => null,
    //     ]);

    //     // 3. Update booking status to 'Confirmed'
    //     $booking = $payment->booking;
    //     $booking->update([
    //         'booking_status' => 'Confirmed',
    //     ]);

    //     // 4. DATABASE CHECK: Find or Create Invoice to avoid duplicate key errors
    //     $invoiceData = Invoice::where('bookingID', $booking->bookingID)->first();

    //     if (!$invoiceData) {
    //         // Create a new record in the 'invoice' table
    //         $invoiceData = Invoice::create([
    //             'invoice_number' => 'INV-' . date('Ymd') . '-' . $booking->bookingID,
    //             'issue_date'     => now(),
    //             'totalAmount'    => $booking->total_price, 
    //             'bookingID'      => $booking->bookingID,
    //             'staffID'        => Auth::id(), // Linked to Staff ID 7 you created
    //         ]);
    //     }

    //     // 5. Loyalty and Voucher Logic
    //     try {
    //         $customerID = $booking->customerID;
    //         $bookingID  = $booking->bookingID;
    //         $staffID    = Auth::id();

    //         $start = Carbon::parse($booking->start_date);
    //         $end   = Carbon::parse($booking->end_date);
    //         $totalHours = $start->diffInHours($end);

    //         $stampsToAward = $totalHours >= 9 ? intdiv($totalHours, 3) : 0;

    //         if ($stampsToAward > 0) {
    //             $stampsTable = Schema::hasTable('stamps') ? 'stamps' : (Schema::hasTable('stamp') ? 'stamp' : null);
    //             if ($stampsTable) {
    //                 $stampData = [];
    //                 if (Schema::hasColumn($stampsTable, 'customerID')) $stampData['customerID'] = $customerID;
    //                 if (Schema::hasColumn($stampsTable, 'bookingID'))  $stampData['bookingID']  = $bookingID;
    //                 if (Schema::hasColumn($stampsTable, 'staffID'))    $stampData['staffID']    = $staffID;
    //                 if (Schema::hasColumn($stampsTable, 'stamps_awarded')) {
    //                     $stampData['stamps_awarded'] = $stampsToAward;
    //                 } elseif (Schema::hasColumn($stampsTable, 'stamps')) {
    //                     $stampData['stamps'] = $stampsToAward;
    //                 }
    //                 if (Schema::hasColumn($stampsTable, 'created_at')) $stampData['created_at'] = now();

    //                 if (!empty($stampData)) {
    //                     DB::table($stampsTable)->insert($stampData);

    //                     $totalStamps = DB::table($stampsTable)
    //                         ->where('customerID', $customerID)
    //                         ->sum(Schema::hasColumn($stampsTable, 'stamps_awarded') ? 'stamps_awarded' : (Schema::hasColumn($stampsTable, 'stamps') ? 'stamps' : DB::raw('0')));

    //                     if ($totalStamps >= 48) {
    //                         $vouchersTable = Schema::hasTable('vouchers') ? 'vouchers' : (Schema::hasTable('voucher') ? 'voucher' : null);
    //                         if ($vouchersTable) {
    //                             $code = 'VCH-' . Str::upper(Str::random(10));
    //                             $voucherData = [];
    //                             if (Schema::hasColumn($vouchersTable, 'customerID'))   $voucherData['customerID']   = $customerID;
    //                             if (Schema::hasColumn($vouchersTable, 'voucher_code')) $voucherData['voucher_code'] = $code;
    //                             elseif (Schema::hasColumn($vouchersTable, 'code'))     $voucherData['code']         = $code;
    //                             if (Schema::hasColumn($vouchersTable, 'label'))        $voucherData['label']        = '1 Free Day';
    //                             elseif (Schema::hasColumn($vouchersTable, 'description')) $voucherData['description'] = '1 Free Day';
    //                             if (Schema::hasColumn($vouchersTable, 'valid_days'))   $voucherData['valid_days']   = 'Mon-Fri';
    //                             elseif (Schema::hasColumn($vouchersTable, 'weekday_only')) $voucherData['weekday_only'] = true;
    //                             if (Schema::hasColumn($vouchersTable, 'staffID'))      $voucherData['staffID']      = $staffID;
    //                             elseif (Schema::hasColumn($vouchersTable, 'issued_by')) $voucherData['issued_by']    = $staffID;
    //                             if (Schema::hasColumn($vouchersTable, 'created_at'))   $voucherData['created_at']   = now();

    //                             if (!empty($voucherData)) {
    //                                 DB::table($vouchersTable)->insert($voucherData);
    //                             }
    //                         }
    //                     }
    //                 }
    //             }
    //         }
    //     } catch (\Throwable $e) {
    //         logger()->warning('Loyalty/Voucher processing failed: '.$e->getMessage());
    //     }

    //     // 6. Generate PDF for the attachment
    //     $pdf = Pdf::loadView('pdf.invoice', compact('booking', 'invoiceData'));

    //     // 7. SEND GMAIL: This triggers the email delivery to the customer
    //     try {
    //         Mail::to($booking->customer->email)->send(new BookingInvoiceMail($booking, $pdf));
    //     } catch (\Exception $e) {
    //         // If Gmail fails but DB is saved, we still redirect with a warning
    //         return redirect()
    //             ->route('admin.payments.index')
    //             ->with('error', 'Payment verified, but email failed: ' . $e->getMessage());
    //     }

    //     return redirect()
    //         ->route('admin.payments.index')
    //         ->with('success', 'Payment verified. Invoice generated and emailed to ' . $booking->customer->email);
    // }

    // /**
    //  * Display the details of a specific payment.
    //  */
    // public function show($id)
    // {
    //     $payment = Payment::where('paymentID', $id)
    //                       ->with(['booking.customer', 'booking.vehicle'])
    //                       ->firstOrFail();

    //     return view('admin.payments.show', compact('payment'));
    // }

    /**
     * Reject a payment and provide a reason.
     */
    public function reject($id): RedirectResponse
    {
        $payment = Payment::where('paymentID', $id)->firstOrFail();

        $payment->update([
            'status' => 'Rejected',
            'verified_by' => Auth::id(),
            'rejected_reason' => 'Receipt rejected by Admin. Please upload a clear copy.',
        ]);

        return redirect()
            ->route('admin.payments.index')
            ->with('success', 'Payment rejected. Customer has been notified.');
    }

    /**
     * Manually generate and download a PDF invoice from the admin panel.
     */
    public function generateInvoice($id)
    {
        $payment = Payment::where('paymentID', $id)->firstOrFail();
        $booking = $payment->booking;
        
        // Find existing invoice record for the PDF data
        $invoiceData = Invoice::where('bookingID', $booking->bookingID)->first();

        $pdf = Pdf::loadView('pdf.invoice', compact('booking', 'invoiceData'));
        return $pdf->download('Invoice-'.$booking->bookingID.'.pdf');
    }
}
