<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class BookingInvoiceMail extends Mailable
{
    use SerializesModels;

    public $booking;
    public $pdf;

    public function __construct($booking, $pdf)
    {
        $this->booking = $booking;
        $this->pdf = $pdf;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: 'cheongyishien@graduate.utm.my',
            subject: 'Payment Verified - Invoice for Booking #' . $this->booking->bookingID,
        );
    }

    public function content(): Content
    {
        // FIX: Use the simple email view we just created
        return new Content(view: 'emails.invoice');
    }

    public function attachments(): array
    {
        if (!$this->pdf) {
            return [];
        }

        return [
            Attachment::fromData(fn () => $this->pdf->output(), 'Invoice_' . $this->booking->bookingID . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}