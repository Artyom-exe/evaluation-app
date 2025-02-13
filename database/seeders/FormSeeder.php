<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Form;
use App\Models\Module;

class FormSeeder extends Seeder
{
    public function run()
    {
        $modules = Module::all();
        $statuses = ['draft', 'pending', 'completed'];

        foreach ($modules as $module) {
            // Créer 2-3 formulaires par module
            for ($i = 1; $i <= rand(2, 3); $i++) {
                Form::create([
                    'title' => "Evaluation {$module->name} - Partie {$i}",
                    'statut' => $statuses[array_rand($statuses)],
                    'module_id' => $module->id
                ]);
            }
        }
    }
}
