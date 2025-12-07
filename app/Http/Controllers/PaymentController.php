<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentRequest;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PaymentController extends Controller
{
    /**
     * Show the form for creating a new payment.
     */
    public function create(Request $request, Booking $booking): View
    {
        // Ensure the booking belongs to the authenticated user
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this booking.');
        }

        // Load relationships
        $booking->load(['vehicle', 'user']);

        // Calculate deposit based on business rules
        $depositAmount = $this->calculateDeposit($booking);

        return view('payments.create', [
            'booking' => $booking,
            'depositAmount' => $depositAmount,
        ]);
    }

    /**
     * Store a newly created payment in storage.
     */
    public function store(PaymentRequest $request): RedirectResponse
    {
        $booking = Booking::findOrFail($request->booking_id);

        // Ensure the booking belongs to the authenticated user
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this booking.');
        }

        // Calculate deposit amount
        $depositAmount = $this->calculateDeposit($booking);

        // Handle file upload
        $proofPath = null;
        if ($request->hasFile('proof_of_payment')) {
            $file = $request->file('proof_of_payment');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $proofPath = $file->storeAs('receipts', $fileName, 'public');
        }

        // Create payment record
        // US017: Status set to "Pending" which represents "Awaiting Payment Verification"
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'amount' => $depositAmount,
            'payment_type' => $booking->duration_days >= 15 ? 'Full Payment' : 'Deposit',
            'payment_method' => $request->payment_method,
            'proof_of_payment' => $proofPath,
            'status' => 'Pending', // US017: This represents "Awaiting Payment Verification"
            'payment_date' => $request->payment_date,
        ]);

        // US017: Booking status should reflect "Awaiting Payment Verification"
        // Note: We keep booking status as "Pending" which aligns with the requirement

        return redirect()
            ->route('dashboard')
            ->with('success', 'Payment submitted successfully! Your payment is now awaiting verification. You will be notified once it is reviewed.');
    }

    /**
     * Calculate deposit amount based on business rules.
     *
     * @param Booking $booking
     * @return float
     */
    private function calculateDeposit(Booking $booking): float
    {
        // Business Rule:
        // - If rental duration < 15 days: Required Deposit = RM 50.00
        // - If rental duration >= 15 days: Required Deposit = 100% of Rental Price
        if ($booking->duration_days < 15) {
            return 50.00;
        }

        return (float) $booking->total_price;
    }
}
