<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            // Admin
            [
                'username' => 'admin',
                'email' => 'admin@hasta.com',
                'password' => Hash::make('password123'),
                'name' => 'Ahmad Admin',
                'phone' => '0123456789',
                'DOB' => '1990-01-01',
                'age' => 34,
                'dateRegistered' => now(),
                'lastLogin' => now(),
                'isActive' => true,
            ],
            // Staff
            [
                'username' => 'staff',
                'email' => 'staff@hasta.com',
                'password' => Hash::make('password123'),
                'name' => 'Siti Staff',
                'phone' => '0123456788',
                'DOB' => '1991-02-02',
                'age' => 33,
                'dateRegistered' => now(),
                'lastLogin' => now(),
                'isActive' => true,
            ],
            // Customers
            [
                'username' => 'ali',
                'email' => 'ali@customer.com',
                'password' => Hash::make('password123'),
                'name' => 'Ali Customer',
                'phone' => '0123456787',
                'DOB' => '1992-03-03',
                'age' => 32,
                'dateRegistered' => now(),
                'lastLogin' => now(),
                'isActive' => true,
            ],
            [
                'username' => 'aminah',
                'email' => 'aminah@customer.com',
                'password' => Hash::make('password123'),
                'name' => 'Aminah Customer',
                'phone' => '0123456786',
                'DOB' => '1993-04-04',
                'age' => 31,
                'dateRegistered' => now(),
                'lastLogin' => now(),
                'isActive' => true,
            ],
            [
                'username' => 'muthu',
                'email' => 'muthu@customer.com',
                'password' => Hash::make('password123'),
                'name' => 'Muthu Customer',
                'phone' => '0123456785',
                'DOB' => '1994-05-05',
                'age' => 30,
                'dateRegistered' => now(),
                'lastLogin' => now(),
                'isActive' => true,
            ],
            [
                'username' => 'lee',
                'email' => 'lee@customer.com',
                'password' => Hash::make('password123'),
                'name' => 'Lee Customer',
                'phone' => '0123456784',
                'DOB' => '1995-06-06',
                'age' => 29,
                'dateRegistered' => now(),
                'lastLogin' => now(),
                'isActive' => true,
            ],
        ];

        foreach ($users as $userData) {
            User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}

