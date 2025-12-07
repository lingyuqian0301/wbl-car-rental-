<?php
// Setup test users for Payment & Billing modules
// Run: php setup_test_users.php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "=== Setting Up Test Users ===\n\n";

// Test Users to Create
$testUsers = [
    [
        'name' => 'Admin User',
        'email' => 'admin@hasta.com',
        'password' => 'password123',
        'role' => 'Admin/Staff'
    ],
    [
        'name' => 'Customer User',
        'email' => 'customer@hasta.com',
        'password' => 'password123',
        'role' => 'Customer'
    ],
];

foreach ($testUsers as $userData) {
    $user = User::where('email', $userData['email'])->first();
    
    if (!$user) {
        $user = User::create([
            'name' => $userData['name'],
            'email' => $userData['email'],
            'password' => Hash::make($userData['password']),
            'email_verified_at' => now(),
        ]);
        echo "✅ Created: {$userData['name']}\n";
    } else {
        $user->update([
            'password' => Hash::make($userData['password']),
        ]);
        echo "🔄 Updated: {$userData['name']}\n";
    }
    
    echo "   Email: {$userData['email']}\n";
    echo "   Password: {$userData['password']}\n";
    echo "   Role: {$userData['role']}\n\n";
}

echo "=== Login Credentials ===\n\n";
echo "For Admin/Staff (Payment Verification):\n";
echo "  Email: admin@hasta.com\n";
echo "  Password: password123\n\n";

echo "For Customer (Payment Submission):\n";
echo "  Email: customer@hasta.com\n";
echo "  Password: password123\n\n";

echo "✅ All test users are ready!\n";

