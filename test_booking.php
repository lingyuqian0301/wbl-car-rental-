<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$booking = App\Models\Booking::find(3);

if ($booking) {
    echo "Booking ID: " . $booking->bookingID . "\n";
    
    // Check vehicle condition forms
    $forms = $booking->vehicleConditionForms;
    echo "Vehicle Condition Forms: " . $forms->count() . "\n";
    
    $pickupForm = $forms->where('form_type', 'RECEIVE')->first();
    echo "Pickup Form (RECEIVE): " . ($pickupForm ? 'Found - ID: ' . $pickupForm->formID : 'NOT FOUND') . "\n";
    
    // Check payments
    $payments = $booking->payments;
    echo "Payments: " . $payments->count() . "\n";
    
    // Check transactions variable (used in view)
    $transactions = $booking->payments()->orderBy('payment_date', 'desc')->get();
    echo "Transactions (ordered): " . $transactions->count() . "\n";
} else {
    echo "Booking ID 3 not found\n";
}


