<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\PersonDetails; // Ensure this line appears ONLY ONCE
use App\Models\Admin;         // Ensure this line appears ONLY ONCE
use Illuminate\Support\Facades\Hash;

echo "=== Creating Test Data for Payment & Billing Modules ===\n\n";

// 1. Create PersonDetails (Required because 'admin' table has a foreign key 'ic_no')
$adminIc = '900101011111'; // Example IC for admin
PersonDetails::firstOrCreate(
    ['ic_no' => $adminIc],
    ['fullname' => 'Admin User']
);

// 2. Get or create the User
$adminUser = User::firstOrCreate(
    ['email' => 'admin@hasta.com'],
    [
        'name' => 'Admin User',
        'password' => Hash::make('password123'),
        'email_verified_at' => now(),
        'phone' => '0123456789',
        // 'role' => 'admin' // REMOVE THIS: Column does not exist and is not fillable
    ]
);

// 3. Create the Admin entry linking User and PersonDetails
// This is what actually makes the user an Admin in your system
Admin::firstOrCreate(
    ['userID' => $adminUser->userID],
    ['ic_no' => $adminIc]
);

echo "✅ Admin User Ready: {$adminUser->email}\n";