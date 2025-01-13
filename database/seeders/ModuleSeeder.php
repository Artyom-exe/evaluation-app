<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;

class ModuleSeeder extends Seeder
{
    public function run()
    {
        Module::create(['name' => 'Mathematics', 'description' => 'Advanced Math Course', 'professor_id' => 1, 'year_id' => 1]);
        Module::create(['name' => 'Physics', 'description' => 'Intro to Physics', 'professor_id' => 2, 'year_id' => 1]);
    }
}
