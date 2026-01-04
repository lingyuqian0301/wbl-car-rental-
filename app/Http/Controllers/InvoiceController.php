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
        $booking = Booking::with(['customer.user', 'vehicle', 'payments', 'invoice'])
            ->findOrFail($bookingId);

        // Check if payment is verified
        $verifiedPayment = $booking->payments()
            ->where('payment_status', 'Verified')
            ->where('payment_isVerify', true)
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

        // Get customer and user info
        $customer = $booking->customer;
        $user = $customer ? $customer->user : null;

        // Get voucher used for this booking
        $voucher = \App\Models\Voucher::where('bookingID', $booking->bookingID)->first();

        // Calculate payment summary
        $verifiedPayments = $booking->payments()
            ->where('payment_status', 'Verified')
            ->where('payment_isVerify', true)
            ->get();

        $totalPaid = $verifiedPayments->sum('total_amount');
        $depositAmount = $booking->deposit_amount ?? 0;
        $rentalAmount = $booking->rental_amount ?? 0;
        $balanceDue = max(0, ($depositAmount + $rentalAmount) - $totalPaid);

        $data = [
            'booking' => $booking,
            'customer' => $customer,
            'user' => $user,
            'voucher' => $voucher,
            'depositAmount' => $depositAmount,
            'rentalAmount' => $rentalAmount,
            'totalPaid' => $totalPaid,
            'balanceDue' => $balanceDue,
            'invoiceData' => $booking->invoice,
            'invoiceDate' => now(),
        ];

        try {
            $pdf = Pdf::loadView('pdf.invoice', $data);

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
