<!DOCTYPE html>
<html>
<head>
    <title>Invoice from HASTA Travel</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <h2>Hello, {{ $booking->customer->user->name ?? 'Customer' }}</h2>
    
    <p>Thank you for choosing HASTA Travel.</p>
    
    <p>We have verified your payment for Booking <strong>#{{ $booking->bookingID }}</strong>.</p>
    
    <p><strong>Booking Details:</strong><br>
    Vehicle: {{ $booking->vehicle->vehicle_brand ?? '' }} {{ $booking->vehicle->vehicle_model ?? '' }}<br>
    Dates: {{ \Carbon\Carbon::parse($booking->rental_start_date ?? $booking->start_date)->format('d M Y') }} - {{ \Carbon\Carbon::parse($booking->rental_end_date ?? $booking->end_date)->format('d M Y') }}
    </p>

    <p>Please find your official invoice attached to this email.</p>

    <p>Safe travels!<br>
    <strong>HASTA Travel Team</strong></p>
</body>
</html>