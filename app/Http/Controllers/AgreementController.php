<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Mpdf\Mpdf;

class AgreementController extends Controller
{
    /**
     * Show the rental agreement page with preview iframe
     */
    public function show(Booking $booking)
    {
        // Ensure the booking belongs to the authenticated user
        if ($booking->customer->userID !== auth()->id()) {
            abort(403, 'Unauthorized access to this booking');
        }

        return view('bookings.agreement', compact('booking'));
    }

    /**
     * Preview the agreement as HTML (for iframe display)
     */
    public function preview(Booking $booking)
    {
        // Ensure the booking belongs to the authenticated user
        if ($booking->customer->userID !== auth()->id()) {
            abort(403, 'Unauthorized access to this booking');
        }

        $customer = $booking->customer;
        $vehicle = $booking->vehicle;

        return view('bookings.agreement_pdf', compact('booking', 'customer', 'vehicle'));
    }

    /**
     * Download the agreement as PDF using mPDF
     */
    public function download(Request $request, Booking $booking)
    {
        // Ensure the booking belongs to the authenticated user
        if ($booking->customer->userID !== auth()->id()) {
            abort(403, 'Unauthorized access to this booking');
        }

        // Validate that the user has agreed to the terms
        $validated = $request->validate([
            'agree' => 'required|accepted',
        ], [
            'agree.required' => 'You must accept the rental agreement to proceed.',
            'agree.accepted' => 'You must accept the rental agreement to proceed.',
        ]);

        // Get booking relationships
        $customer = $booking->customer;
        $vehicle = $booking->vehicle;

        // Render the agreement HTML from Blade view
        $html = view('bookings.agreement_pdf', compact('booking', 'customer', 'vehicle'))->render();

        // Initialize mPDF with proper configuration for legal documents
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 15,
            'margin_bottom' => 15,
        ]);

        // Write the HTML to the PDF
        $mpdf->WriteHTML($html);

        // Return the PDF as a download response
        return response()->streamDownload(
            function () use ($mpdf) {
                echo $mpdf->Output('', 'S');
            },
            'agreement.pdf',
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="agreement.pdf"',
            ]
        );
    }
}

