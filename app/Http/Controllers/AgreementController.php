<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Mpdf\Mpdf;

class AgreementController extends Controller
{
    public function show(Booking $booking)
    {
        if ($booking->customer->userID !== auth()->id()) {
            abort(403);
        }
        return view('bookings.agreement', compact('booking'));
    }

    /**
     * Preview the agreement (Logic to fetch Identity & Phone)
     */
    public function preview(Booking $booking)
    {
        // 1. Security Check
        if ($booking->customer->userID !== auth()->id()) {
            abort(403);
        }

        // 2. Load ALL Relationships (Local, International, User, Vehicle)
        $booking->load(['customer.user', 'customer.local', 'customer.international', 'vehicle']);

        // 3. Determine Identity Number (IC or Passport)
        $identityNo = 'N/A';
        if ($booking->customer->local) {
            $identityNo = $booking->customer->local->ic_no; // Get IC
        } elseif ($booking->customer->international) {
            $identityNo = $booking->customer->international->passport_no; // Get Passport
        }

        // 4. Determine Phone Number (Check Customer Profile first, then User Account)
        $phone = $booking->customer->phone_number;
        if (empty($phone) || $phone == 'N/A') {
            $phone = $booking->customer->user->phone ?? 'N/A';
        }

        // 5. Pass these specific variables to the PDF view
        return view('bookings.agreement_pdf', [
            'booking' => $booking,
            'customer' => $booking->customer,
            'vehicle' => $booking->vehicle,
            'identityNo' => $identityNo, // <--- Passed to view
            'phone' => $phone            // <--- Passed to view
        ]);
    }

    /**
     * Download the agreement (Same logic as preview)
     */
    public function download(Request $request, Booking $booking)
    {
        if ($booking->customer->userID !== auth()->id()) {
            abort(403);
        }

        $request->validate(['agree' => 'required|accepted']);

        // Load Relationships
        $booking->load(['customer.user', 'customer.local', 'customer.international', 'vehicle']);

        // Get ID
        $identityNo = 'N/A';
        if ($booking->customer->local) {
            $identityNo = $booking->customer->local->ic_no;
        } elseif ($booking->customer->international) {
            $identityNo = $booking->customer->international->passport_no;
        }

        // Get Phone
        $phone = $booking->customer->phone_number;
        if (empty($phone) || $phone == 'N/A') {
            $phone = $booking->customer->user->phone ?? 'N/A';
        }

        // Generate PDF
        $html = view('bookings.agreement_pdf', [
            'booking' => $booking,
            'customer' => $booking->customer,
            'vehicle' => $booking->vehicle,
            'identityNo' => $identityNo,
            'phone' => $phone
        ])->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8', 'format' => 'A4', 'orientation' => 'P',
            'margin_left' => 15, 'margin_right' => 15, 'margin_top' => 15, 'margin_bottom' => 15,
        ]);

        $mpdf->WriteHTML($html);

        return response()->streamDownload(
            function () use ($mpdf) { echo $mpdf->Output('', 'S'); },
            'Agreement.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

    /**
     * Upload signed rental agreement and redirect to pickup
     */
    public function upload(Request $request, Booking $booking)
    {
        // Security Check
        if ($booking->customer->userID !== auth()->id()) {
            abort(403);
        }

        // Validate file upload
        $request->validate([
            'signed_agreement' => 'required|file|mimes:jpeg,jpg,png,gif,pdf|max:10240',
        ], [
            'signed_agreement.required' => 'Please upload the signed rental agreement.',
            'signed_agreement.mimes' => 'The file must be an image (JPEG, PNG, GIF) or PDF.',
            'signed_agreement.max' => 'The file size must not exceed 10MB.',
        ]);

        // Store the file
        $destinationPath = public_path('images/signed_agreements');
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        $file = $request->file('signed_agreement');
        $filename = 'agreement_' . $booking->bookingID . '_' . time() . '.' . $file->getClientOriginalExtension();
        $file->move($destinationPath, $filename);

        // Store path in booking (you may want to add a column for this)
        // For now, we'll just redirect to pickup
        // $booking->signed_agreement_path = 'images/signed_agreements/' . $filename;
        // $booking->save();

        return redirect()->route('pickup.show', $booking)
            ->with('success', 'Signed agreement uploaded successfully. You can now proceed with vehicle pickup.');
    }
}