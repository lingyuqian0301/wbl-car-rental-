<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Balance Payment Reminder</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #dc2626;">Balance Payment Reminder</h2>
        
        <p>Dear {{ $booking->user->name }},</p>
        
        <p>This is a reminder regarding your booking #{{ $booking->id }} for <strong>{{ $booking->vehicle->full_model }}</strong> ({{ $booking->vehicle->registration_number }}).</p>
        
        <div style="background: #fee2e2; padding: 15px; border-radius: 8px; margin: 20px 0;">
            <h3 style="margin-top: 0; color: #dc2626;">Payment Summary</h3>
            <p><strong>Total Booking Amount:</strong> RM {{ number_format($booking->total_price, 2) }}</p>
            <p><strong>Amount Paid:</strong> RM {{ number_format($booking->payments()->where('payment_status', 'Verified')->sum('amount'), 2) }}</p>
            <p><strong style="color: #dc2626; font-size: 1.2em;">Balance Due:</strong> RM {{ number_format($balanceDue, 2) }}</p>
        </div>
        
        <p>Please complete your balance payment to confirm your booking. You can make the payment through your booking dashboard.</p>
        
        <p style="margin-top: 30px;">
            <a href="{{ route('bookings.show', $booking->id) }}" 
               style="background: #dc2626; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;">
                View Booking & Pay Balance
            </a>
        </p>
        
        <p style="margin-top: 30px; color: #666; font-size: 0.9em;">
            If you have any questions, please contact our support team.<br>
            Thank you for choosing Hasta Car Rental.
        </p>
    </div>
</body>
</html>












