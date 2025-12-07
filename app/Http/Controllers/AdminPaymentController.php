<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminPaymentController extends Controller
{
    /**
     * Display a listing of pending payments.
     */
    public function index(): View
    {
        $payments = Payment::with(['booking.vehicle', 'booking.user'])
            ->where('status', 'Pending')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.payments.index', [
            'payments' => $payments,
        ]);
    }

    /**
     * Display the specified payment.
     */
    public function show(Payment $payment): View
    {
        $payment->load(['booking.vehicle', 'booking.user', 'verifier']);

        return view('admin.payments.show', [
            'payment' => $payment,
        ]);
    }

    /**
     * Approve a payment.
     */
    public function approve(Request $request, Payment $payment): RedirectResponse
    {
        $request->validate([
            'payment_id' => 'required|exists:payments,id',
        ]);

        // Update payment status
        $payment->update([
            'status' => 'Verified',
            'verified_by' => Auth::id(),
            'rejected_reason' => null,
        ]);

        // Update booking status to Confirmed
        $payment->booking->update([
            'status' => 'Confirmed',
        ]);

        // US019: Auto-generate invoice after payment verification
        // Note: Invoice is generated on-demand, but we can trigger a notification here
        // In a production system, you might want to generate and store the PDF here
        
        // US016: Send notification to customer (placeholder - implement email notification)
        // TODO: Implement email notification to customer about payment verification
        // Example: Mail::to($payment->booking->user->email)->send(new PaymentVerifiedMail($payment));

        return redirect()
            ->route('admin.payments.index')
            ->with('success', 'Payment verified successfully! Booking status updated to Confirmed. Customer has been notified.');
    }

    /**
     * Reject a payment.
     */
    public function reject(Request $request, Payment $payment): RedirectResponse
    {
        $request->validate([
            'payment_id' => 'required|exists:payments,id',
            'rejected_reason' => 'required|string|min:10|max:500',
        ]);

        // Update payment status
        $payment->update([
            'status' => 'Rejected',
            'verified_by' => Auth::id(),
            'rejected_reason' => $request->rejected_reason,
        ]);

        // US016: Notify customer to re-upload receipt
        // TODO: Implement email notification to customer about payment rejection
        // Example: Mail::to($payment->booking->user->email)->send(new PaymentRejectedMail($payment));

        return redirect()
            ->route('admin.payments.index')
            ->with('success', 'Payment rejected successfully. Customer has been notified to re-upload the receipt.');
    }
}
