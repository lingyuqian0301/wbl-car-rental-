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
        // 1. Find Payment by custom primary key 'paymentID'
        $payment = Payment::where('paymentID', $id)->firstOrFail();

        // 2. Update payment status and record the staff who verified it
        $payment->update([
            'status' => 'Verified',
            'verified_by' => Auth::id(),
            'rejected_reason' => null,
        ]);

        // 3. Update booking status to 'Confirmed'
        $booking = $payment->booking;
        $booking->update([
            'booking_status' => 'Confirmed',
        ]);

        // 4. DATABASE CHECK: Find or Create Invoice to avoid duplicate key errors
        $invoiceData = Invoice::where('bookingID', $booking->bookingID)->first();

        if (!$invoiceData) {
            // Create a new record in the 'invoice' table
            $invoiceData = Invoice::create([
                'invoice_number' => 'INV-' . date('Ymd') . '-' . $booking->bookingID,
                'issue_date'     => now(),
                'totalAmount'    => $booking->total_price, 
                'bookingID'      => $booking->bookingID,
                'staffID'        => Auth::id(), // Linked to Staff ID 7 you created
            ]);
        }

        // 5. Generate PDF for the attachment
        $pdf = Pdf::loadView('pdf.invoice', compact('booking', 'invoiceData'));

        // 6. SEND GMAIL: This triggers the email delivery to the customer
        try {
            Mail::to($booking->customer->email)->send(new BookingInvoiceMail($booking, $pdf));
        } catch (\Exception $e) {
            // If Gmail fails but DB is saved, we still redirect with a warning
            return redirect()
                ->route('admin.payments.index')
                ->with('error', 'Payment verified, but email failed: ' . $e->getMessage());
        }

        return redirect()
            ->route('admin.payments.index')
            ->with('success', 'Payment verified. Invoice generated and emailed to ' . $booking->customer->email);
    }

    /**
     * Display the details of a specific payment.
     */
    public function show($id)
    {
        $payment = Payment::where('paymentID', $id)
                          ->with(['booking.customer', 'booking.vehicle'])
                          ->firstOrFail();

        return view('admin.payments.show', compact('payment'));
    }

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