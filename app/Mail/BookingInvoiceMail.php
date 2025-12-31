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

    /**
     * Receive the booking and PDF from the Controller
     */
    public function __construct($booking, $pdf)
    {
        $this->booking = $booking;
        $this->pdf = $pdf;
    }

    /**
     * Set the email subject
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Hasta Travel Invoice - Booking #' . $this->booking->bookingID,
        );
    }

    /**
     * Point to the correct blade view
     */
    public function content(): Content
    {
        return new Content(
            view: 'pdf.invoice', // This uses your resources/views/pdf/invoice.blade.php
        );
    }

    /**
     * Attach the generated PDF to the Gmail
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdf->output(), 'Invoice_' . $this->booking->bookingID . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}