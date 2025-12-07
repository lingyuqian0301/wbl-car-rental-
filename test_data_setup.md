# Quick Test Data Setup

## Option 1: Using Tinker (Recommended)

Run these commands in `php artisan tinker`:

```php
// Create a test vehicle
$vehicle = \App\Models\Vehicle::create([
    'brand' => 'Toyota',
    'model' => 'Vios',
    'registration_number' => 'ABC1234',
    'daily_rate' => 150.00,
    'status' => 'Available',
]);

// Create a test booking (replace user_id with your actual user ID)
$booking = \App\Models\Booking::create([
    'user_id' => 1, // Change to your user ID
    'vehicle_id' => $vehicle->id,
    'start_date' => now()->addDays(7),
    'end_date' => now()->addDays(14),
    'duration_days' => 7,
    'total_price' => 1050.00,
    'status' => 'Pending',
]);

// Create a test payment
$payment = \App\Models\Payment::create([
    'booking_id' => $booking->id,
    'amount' => 50.00,
    'payment_type' => 'Deposit',
    'payment_method' => 'Bank Transfer',
    'proof_of_payment' => null, // Upload via form
    'status' => 'Pending',
    'payment_date' => now(),
]);
```

## Option 2: Direct Database Insert

You can also insert data directly via phpMyAdmin or SQL.

