<?php

namespace Database\Seeders;

use App\Models\PersonDetails;
use Illuminate\Database\Seeder;

class PersonDetailsSeeder extends Seeder
{
    public function run(): void
    {
        $persons = [
            ['ic_no' => '900101012345', 'fullname' => 'Ahmad Admin'],
            ['ic_no' => '910202023456', 'fullname' => 'Siti Staff'],
            ['ic_no' => '920303034567', 'fullname' => 'Ali Customer'],
            ['ic_no' => '930404045678', 'fullname' => 'Aminah Customer'],
            ['ic_no' => '940505056789', 'fullname' => 'Muthu Customer'],
            ['ic_no' => '950606067890', 'fullname' => 'Lee Customer'],
            ['ic_no' => '880101011111', 'fullname' => 'Owner Razak'],
            ['ic_no' => '870202022222', 'fullname' => 'Owner Lim'],
        ];

        foreach ($persons as $person) {
            PersonDetails::firstOrCreate(
                ['ic_no' => $person['ic_no']],
                $person
            );
        }
    }
}

