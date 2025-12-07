<?php
// Quick script to create a test user
// Run: php create_user.php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "=== User Management ===\n\n";

// Check existing users
$users = User::all();
echo "Existing users: " . $users->count() . "\n";
foreach ($users as $user) {
    echo "  - ID: {$user->id}, Name: {$user->name}, Email: {$user->email}\n";
}

echo "\n";

// Create a test user if needed
$email = 'admin@hasta.com';
$password = 'password123';

$user = User::where('email', $email)->first();

if (!$user) {
    echo "Creating test user...\n";
    $user = User::create([
        'name' => 'Admin User',
        'email' => $email,
        'password' => Hash::make($password),
        'email_verified_at' => now(),
    ]);
    echo "✅ User created successfully!\n";
} else {
    echo "User already exists. Resetting password...\n";
    $user->update([
        'password' => Hash::make($password),
    ]);
    echo "✅ Password reset!\n";
}

echo "\n=== Login Credentials ===\n";
echo "Email: {$user->email}\n";
echo "Password: {$password}\n";
echo "\nYou can now login with these credentials.\n";

