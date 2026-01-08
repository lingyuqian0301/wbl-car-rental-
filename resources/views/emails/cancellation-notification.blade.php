<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Cancellation Update</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #b91c1c;">Booking Cancellation Update</h2>
        
        <p>Dear {{ $customer->name ?? 'Customer' }},</p>
        
        <p>We are writing to inform you about an update regarding your booking cancellation.</p>
        
        <div style="background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <h3 style="margin-top: 0;">Booking Details</h3>
            <p><strong>Booking ID:</strong> #{{ $booking->bookingID }}</p>
            <p><strong>Status:</strong> {{ $booking->booking_status }}</p>
            @if($booking->vehicle)
                <p><strong>Vehicle:</strong> {{ $booking->vehicle->vehicle_brand ?? '' }} {{ $booking->vehicle->vehicle_model ?? '' }}</p>
                <p><strong>Plate Number:</strong> {{ $booking->vehicle->plate_number ?? 'N/A' }}</p>
            @endif
            <p><strong>Rental Period:</strong> {{ $booking->rental_start_date?->format('d M Y') ?? 'N/A' }} - {{ $booking->rental_end_date?->format('d M Y') ?? 'N/A' }}</p>
        </div>
        
        <p>If you have any questions or concerns, please don't hesitate to contact us.</p>
        
        <p>Best regards,<br>
        Hasta Travel Team</p>
    </div>
</body>
</html>




