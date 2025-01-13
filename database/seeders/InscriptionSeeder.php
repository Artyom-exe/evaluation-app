<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Inscription;

class InscriptionSeeder extends Seeder
{
    public function run()
    {
        Inscription::create(['student_id' => 1, 'module_id' => 1]);
        Inscription::create(['student_id' => 2, 'module_id' => 2]);
    }
}
