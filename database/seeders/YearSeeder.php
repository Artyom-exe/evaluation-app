<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Year;

class YearSeeder extends Seeder
{
    public function run()
    {
        $years = [
            ['name' => 'L1 2023-2024'],
            ['name' => 'L2 2023-2024'],
            ['name' => 'L3 2023-2024'],
            ['name' => 'M1 2023-2024'],
            ['name' => 'M2 2023-2024'],
        ];

        foreach ($years as $year) {
            Year::create($year);
        }
    }
}
