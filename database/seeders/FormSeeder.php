<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Form;

class FormSeeder extends Seeder
{
    public function run()
    {
        Form::create(['title' => 'Evaluation Math', 'statut' => 'open', 'module_id' => 1]);
        Form::create(['title' => 'Evaluation Physics', 'statut' => 'open', 'module_id' => 2]);
    }
}
