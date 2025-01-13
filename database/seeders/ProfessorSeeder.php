<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Professor;

class ProfessorSeeder extends Seeder
{
    public function run()
    {
        Professor::create(['name' => 'Dr. John Doe', 'email' => 'john.doe@example.com']);
        Professor::create(['name' => 'Dr. Jane Smith', 'email' => 'jane.smith@example.com']);
    }
}
