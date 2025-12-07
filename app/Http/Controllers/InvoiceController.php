<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class InvoiceController extends Controller
{
    /**
     * Generate and download invoice PDF for a booking.
     *
     * @param int $bookingId
     * @return Response
     */
    public function generatePDF(int $bookingId): Response
    {
        $booking = Booking::with(['user', 'vehicle', 'payments'])
            ->findOrFail($bookingId);

        // Check if payment is verified
        $verifiedPayment = $booking->payments()
            ->where('status', 'Verified')
            ->first();

        if (!$verifiedPayment) {
            // US018: Exception flow - Payment not verified
            if (request()->expectsJson()) {
                return response()->json([
                    'error' => 'Receipt not available. Payment is currently being verified.'
                ], 403);
            }
            
            return redirect()
                ->route('bookings.show', $bookingId)
                ->with('error', 'Receipt not available. Payment is currently being verified.');
        }

        // Calculate payment summary
        $depositPaid = $booking->payments()
            ->where('status', 'Verified')
            ->where('payment_type', 'Deposit')
            ->sum('amount');

        $fullPaymentPaid = $booking->payments()
            ->where('status', 'Verified')
            ->where('payment_type', 'Full Payment')
            ->sum('amount');

        $totalPaid = $depositPaid + $fullPaymentPaid;
        $balanceDue = max(0, $booking->total_price - $totalPaid);

        $data = [
            'booking' => $booking,
            'depositPaid' => $depositPaid,
            'fullPaymentPaid' => $fullPaymentPaid,
            'totalPaid' => $totalPaid,
            'balanceDue' => $balanceDue,
            'invoiceDate' => now(),
        ];

        try {
            $pdf = Pdf::loadView('invoices.pdf', $data);
            
            return $pdf->download('invoice-booking-' . $bookingId . '.pdf');
        } catch (\Exception $e) {
            // US018 & US019: Exception flow - PDF generation fails
            \Log::error('PDF Generation Failed: ' . $e->getMessage());
            
            if (request()->expectsJson()) {
                return response()->json([
                    'error' => 'Unable to generate receipt. Please try again later.'
                ], 500);
            }
            
            return redirect()
                ->route('bookings.show', $bookingId)
                ->with('error', 'Unable to generate receipt. Please try again later.');
        }
    }
}
