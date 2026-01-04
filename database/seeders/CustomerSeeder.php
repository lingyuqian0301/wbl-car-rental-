<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Local;
use App\Models\LoyaltyCard;
use App\Models\WalletAccount;
use App\Models\User;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customerEmails = [
            'ali@customer.com' => ['ic_no' => '920303034567', 'state' => 'Johor'],
            'aminah@customer.com' => ['ic_no' => '930404045678', 'state' => 'Selangor'],
            'muthu@customer.com' => ['ic_no' => '940505056789', 'state' => 'Penang'],
            'lee@customer.com' => ['ic_no' => '950606067890', 'state' => 'Perak'],
        ];

        foreach ($customerEmails as $email => $data) {
            $user = User::where('email', $email)->first();

            if ($user) {
                // Create Customer
                $customer = Customer::firstOrCreate(
                    ['userID' => $user->userID],
                    [
                        'userID' => $user->userID,
                        'phone_number' => $user->phone,
                        'address' => 'Jalan ' . $user->name . ', Taman Test, 81310 Skudai, Johor',
                        'customer_license' => 'D' . rand(10000000, 99999999),
                        'emergency_contact' => '01' . rand(10000000, 99999999),
                        'booking_times' => 0,
                    ]
                );

                // Create Local record
                Local::firstOrCreate(
                    ['customerID' => $customer->customerID],
                    [
                        'customerID' => $customer->customerID,
                        'ic_no' => $data['ic_no'],
                        'stateOfOrigin' => $data['state'],
                    ]
                );

                // Create Wallet Account
                WalletAccount::firstOrCreate(
                    ['customerID' => $customer->customerID],
                    [
                        'customerID' => $customer->customerID,
                        'wallet_balance' => rand(50, 500) + (rand(0, 99) / 100),
                        'outstanding_amount' => 0.00,
                        'wallet_status' => 'Active',
                        'wallet_lastUpdate_Date_Time' => now(),
                    ]
                );

                // Create Loyalty Card
                LoyaltyCard::firstOrCreate(
                    ['customerID' => $customer->customerID],
                    [
                        'customerID' => $customer->customerID,
                        'total_stamps' => rand(0, 30),
                        'loyalty_last_updated' => now(),
                    ]
                );
            }
        }
    }
}

