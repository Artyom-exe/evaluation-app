<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Form;
use Illuminate\Support\Facades\DB;

class FormSeeder extends Seeder
{
    public function run()
    {
        // Créer le formulaire
        Form::create([
            'title' => 'Evaluation Math',
            'statut' => 'draft',
            'module_id' => 1
        ]);

        Form::create([
            'title' => 'Evaluation Physique',
            'statut' => 'draft',
            'module_id' => 2
        ]);
    }
}
