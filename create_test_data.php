<?php
// Quick script to create complete test data for Payment & Billing modules
// Run: php create_test_data.php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Hash;

echo "=== Creating Test Data for Payment & Billing Modules ===\n\n";

// Get or create test users
$admin = User::firstOrCreate(
    ['email' => 'admin@hasta.com'],
    [
        'name' => 'Admin User',
        'password' => Hash::make('password123'),
        'email_verified_at' => now(),
        'role' => 'admin', // Set as admin
    ]
);

// Ensure admin role is set (in case user already existed)
if ($admin->role !== 'admin') {
    $admin->update(['role' => 'admin']);
}

$customer = User::firstOrCreate(
    ['email' => 'customer@hasta.com'],
    [
        'name' => 'Customer User',
        'password' => Hash::make('password123'),
        'email_verified_at' => now(),
        'role' => 'customer', // Set as customer
    ]
);

// Ensure customer role is set
if ($customer->role !== 'customer') {
    $customer->update(['role' => 'customer']);
}

echo "✅ Users ready:\n";
echo "   - Admin: {$admin->email} (ID: {$admin->id})\n";
echo "   - Customer: {$customer->email} (ID: {$customer->id})\n\n";

// Create Vehicles
$vehicles = [
    [
        'brand' => 'Toyota',
        'model' => 'Vios',
        'registration_number' => 'ABC1234',
        'daily_rate' => 150.00,
        'status' => 'Available',
    ],
    [
        'brand' => 'Honda',
        'model' => 'City',
        'registration_number' => 'XYZ5678',
        'daily_rate' => 180.00,
        'status' => 'Available',
    ],
    [
        'brand' => 'Proton',
        'model' => 'Saga',
        'registration_number' => 'DEF9012',
        'daily_rate' => 100.00,
        'status' => 'Available',
    ],
];

echo "Creating vehicles...\n";
foreach ($vehicles as $vehicleData) {
    $vehicle = Vehicle::firstOrCreate(
        ['registration_number' => $vehicleData['registration_number']],
        $vehicleData
    );
    echo "   ✅ {$vehicle->full_model} ({$vehicle->registration_number})\n";
}
echo "\n";

// Create Bookings for Customer
echo "Creating bookings for customer...\n";

// Booking 1: Short rental (< 15 days) - Deposit should be RM 50
$booking1 = Booking::create([
    'user_id' => $customer->id,
    'vehicle_id' => Vehicle::where('registration_number', 'ABC1234')->first()->id,
    'start_date' => now()->addDays(7),
    'end_date' => now()->addDays(14),
    'duration_days' => 7,
    'total_price' => 1050.00, // 7 days × RM 150
    'status' => 'Pending',
]);
echo "   ✅ Booking #{$booking1->id}: {$booking1->duration_days} days (Deposit: RM 50.00)\n";

// Booking 2: Long rental (≥ 15 days) - Deposit should be 100% of total
$booking2 = Booking::create([
    'user_id' => $customer->id,
    'vehicle_id' => Vehicle::where('registration_number', 'XYZ5678')->first()->id,
    'start_date' => now()->addDays(10),
    'end_date' => now()->addDays(30),
    'duration_days' => 20,
    'total_price' => 3600.00, // 20 days × RM 180
    'status' => 'Pending',
]);
echo "   ✅ Booking #{$booking2->id}: {$booking2->duration_days} days (Deposit: RM 3600.00 - Full Payment)\n";

// Booking 3: Already has a pending payment
$booking3 = Booking::create([
    'user_id' => $customer->id,
    'vehicle_id' => Vehicle::where('registration_number', 'DEF9012')->first()->id,
    'start_date' => now()->addDays(5),
    'end_date' => now()->addDays(12),
    'duration_days' => 7,
    'total_price' => 700.00, // 7 days × RM 100
    'status' => 'Pending',
]);
echo "   ✅ Booking #{$booking3->id}: {$booking3->duration_days} days (Deposit: RM 50.00)\n";

// Create a pending payment for booking 3
$payment1 = Payment::create([
    'booking_id' => $booking3->id,
    'amount' => 50.00,
    'payment_type' => 'Deposit',
    'payment_method' => 'Bank Transfer',
    'proof_of_payment' => 'receipts/sample_receipt.jpg',
    'status' => 'Pending',
    'payment_date' => now(),
]);
echo "   ✅ Payment created for Booking #{$booking3->id} (Status: Pending)\n";

// Booking 4: Has verified payment (for invoice testing)
$booking4 = Booking::create([
    'user_id' => $customer->id,
    'vehicle_id' => Vehicle::where('registration_number', 'ABC1234')->first()->id,
    'start_date' => now()->addDays(15),
    'end_date' => now()->addDays(22),
    'duration_days' => 7,
    'total_price' => 1050.00,
    'status' => 'Confirmed',
]);
echo "   ✅ Booking #{$booking4->id}: {$booking4->duration_days} days (Status: Confirmed)\n";

// Create verified payment for booking 4
$payment2 = Payment::create([
    'booking_id' => $booking4->id,
    'amount' => 50.00,
    'payment_type' => 'Deposit',
    'payment_method' => 'Bank Transfer',
    'proof_of_payment' => 'receipts/sample_receipt.jpg',
    'status' => 'Verified',
    'verified_by' => $admin->id,
    'payment_date' => now()->subDays(2),
]);
echo "   ✅ Payment created for Booking #{$booking4->id} (Status: Verified - Invoice ready!)\n";

echo "\n=== Test Data Summary ===\n\n";
echo "📋 Bookings Created:\n";
echo "   - Booking #{$booking1->id}: No payment (Ready for payment submission)\n";
echo "   - Booking #{$booking2->id}: No payment (Ready for payment submission - Full Payment)\n";
echo "   - Booking #{$booking3->id}: Pending payment (Ready for staff verification)\n";
echo "   - Booking #{$booking4->id}: Verified payment (Ready for invoice download)\n\n";

echo "👤 Test Users:\n";
echo "   Customer: {$customer->email} / password123\n";
echo "   Admin: {$admin->email} / password123\n\n";

echo "🚗 Vehicles Created: " . Vehicle::count() . "\n";
echo "📦 Bookings Created: " . Booking::count() . "\n";
echo "💳 Payments Created: " . Payment::count() . "\n\n";

echo "✅ Test data ready! You can now:\n";
echo "   1. Login as customer and test payment submission\n";
echo "   2. Login as admin and test payment verification\n";
echo "   3. Download invoice for Booking #{$booking4->id}\n";

