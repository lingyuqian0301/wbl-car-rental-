<?php

namespace Database\Seeders;

use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\Seeder;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        $staffUser = User::where('email', 'staff@hasta.com')->first();

        if ($staffUser) {
            Staff::firstOrCreate(
                ['userID' => $staffUser->userID],
                [
                    'ic_no' => '910202023456',
                    'userID' => $staffUser->userID,
                ]
            );
        }
    }
}
