<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $adminUser = User::where('email', 'admin@hasta.com')->first();

        if ($adminUser) {
            Admin::firstOrCreate(
                ['userID' => $adminUser->userID],
                [
                    'ic_no' => '900101012345',
                    'userID' => $adminUser->userID,
                ]
            );
        }
    }
}

