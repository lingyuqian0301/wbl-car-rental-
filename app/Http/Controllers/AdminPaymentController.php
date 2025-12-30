<?php

namespace App\Http\Controllers;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminPaymentController extends Controller
{
    /**
     * Display a listing of pending payments.
     */
public function index(): View
    {
        // SHOW ALL PAYMENTS (Verified, Rejected, and Pending)
        $payments = Payment::with(['booking.customer', 'booking.vehicle'])
            ->orderBy('created_at', 'desc') // Newest first
            ->paginate(15);

        return view('admin.payments.index', [
            'payments' => $payments,
        ]);
    }
    /**
     * Display the specified payment.
     */
    public function show($id)
    {
        // FIX: Find the payment by its custom ID (paymentID)
        // FIX: Load 'booking.customer'
        $payment = Payment::where('paymentID', $id)
                          ->with(['booking.customer', 'booking.vehicle'])
                          ->firstOrFail();

        return view('admin.payments.show', compact('payment'));
    }

    /**
     * Approve a payment.
     */
    public function approve($id): RedirectResponse
    {
        // 1. Find Payment
        $payment = Payment::where('paymentID', $id)->firstOrFail();

        // 2. Update payment status
        $payment->update([
            'status' => 'Verified',
            'verified_by' => Auth::id(), // Ensure your users table has IDs
            'rejected_reason' => null,
        ]);

        // 3. Update booking status to Confirmed
        // FIX: Column name is 'booking_status', not 'status'
        $payment->booking->update([
            'booking_status' => 'Confirmed',
        ]);

        return redirect()
            ->route('admin.payments.index')
            ->with('success', 'Payment verified successfully! Booking confirmed.');
    }

    /**
     * Reject a payment.
     */
    public function reject($id): RedirectResponse
    {
        // 1. Find Payment
        $payment = Payment::where('paymentID', $id)->firstOrFail();

        // 2. Update payment status
        $payment->update([
            'status' => 'Rejected',
            'verified_by' => Auth::id(),
            // We set a default reason since the view doesn't have an input field yet
            'rejected_reason' => 'Receipt rejected by Admin. Please upload a clear copy.',
        ]);

        return redirect()
            ->route('admin.payments.index')
            ->with('success', 'Payment rejected. Customer has been notified.');
    }
    public function generateInvoice($id)
    {
        // Find payment and booking
        $payment = Payment::where('paymentID', $id)->firstOrFail();
        $booking = $payment->booking;

        // Load the view and download PDF
        $pdf = Pdf::loadView('pdf.invoice', compact('booking'));
        return $pdf->download('Invoice-'.$booking->bookingID.'.pdf');
    }
}
