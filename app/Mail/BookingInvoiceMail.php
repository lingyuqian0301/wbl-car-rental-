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
    use Queueable, SerializesModels;

    public $booking;
    public $pdf;

    // Ensure we require both $booking AND $pdf
    public function __construct($booking, $pdf)
    {
        $this->booking = $booking;
        $this->pdf = $pdf;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Hasta Travel Invoice - Booking #' . $this->booking->bookingID,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'pdf.invoice');
    }

    public function attachments(): array
    {
        // Safety check: if $pdf is somehow missing (like in your preview route), don't crash.
        if (!$this->pdf) {
            return [];
        }

        return [
            Attachment::fromData(fn () => $this->pdf->output(), 'Invoice_' . $this->booking->bookingID . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}