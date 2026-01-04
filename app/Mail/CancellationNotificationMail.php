<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CancellationNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Booking $booking
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Booking Cancellation Update - Booking #' . $this->booking->bookingID,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.cancellation-notification',
            with: [
                'booking' => $this->booking,
                'customer' => $this->booking->customer->user ?? null,
            ],
        );
    }
}
