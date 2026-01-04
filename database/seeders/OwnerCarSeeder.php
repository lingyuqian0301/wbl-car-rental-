<?php

namespace Database\Seeders;

use App\Models\OwnerCar;
use Illuminate\Database\Seeder;

class OwnerCarSeeder extends Seeder
{
    public function run(): void
    {
        $owners = [
            [
                'ic_no' => '880101011111',
                'contact_number' => '0191234567',
                'email' => 'razak@owner.com',
                'bankname' => 'Maybank',
                'bank_acc_number' => '1234567890',
                'registration_date' => now()->subMonths(12),
            ],
            [
                'ic_no' => '870202022222',
                'contact_number' => '0192345678',
                'email' => 'lim@owner.com',
                'bankname' => 'CIMB',
                'bank_acc_number' => '0987654321',
                'registration_date' => now()->subMonths(6),
            ],
        ];

        foreach ($owners as $owner) {
            OwnerCar::firstOrCreate(
                ['ic_no' => $owner['ic_no']],
                $owner
            );
        }
    }
}

