<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Year;

class YearSeeder extends Seeder
{
    public function run()
    {
        Year::create(['name' => '2023-2024']);
        Year::create(['name' => '2024-2025']);
    }
}
