<?php

namespace App\Observers;

use App\Models\Booking;
use App\Services\PaymentService;

class BookingObserver
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Handle the Booking "updated" event.
     */
    public function updated(Booking $booking): void
    {
        // When booking status changes to "Completed" and keep_deposit is true
        if ($booking->status === 'Completed' && $booking->keep_deposit) {
            $this->paymentService->processKeepDeposit($booking);
        }
    }
}







