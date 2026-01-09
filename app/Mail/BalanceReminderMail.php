<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BalanceReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Booking $booking
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Balance Payment Reminder - Booking #' . $this->booking->id,
        );
    }

    public function content(): Content
    {
        $totalPaid = $this->booking->payments()
            ->where('payment_status', 'Verified')
            ->sum('total_amount');
        $balanceDue = max(0, $this->booking->total_price - $totalPaid);

        return new Content(
            view: 'emails.balance-reminder',
            with: [
                'booking' => $this->booking,
                'balanceDue' => $balanceDue,
            ],
        );
    }
}














