<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;

class ModuleSeeder extends Seeder
{
    public function run()
    {
        $modules = [
            ['name' => 'Mathématiques Avancées', 'professor_id' => 1, 'year_id' => 1],
            ['name' => 'Physique Quantique', 'professor_id' => 2, 'year_id' => 1],
            ['name' => 'Programmation Web', 'professor_id' => 3, 'year_id' => 1],
            ['name' => 'Base de données', 'professor_id' => 3, 'year_id' => 2],
            ['name' => 'Intelligence Artificielle', 'professor_id' => 4, 'year_id' => 2],
            ['name' => 'Cybersécurité', 'professor_id' => 5, 'year_id' => 2],
            ['name' => 'Réseaux', 'professor_id' => 4, 'year_id' => 3],
            ['name' => 'DevOps', 'professor_id' => 5, 'year_id' => 3],
        ];

        foreach ($modules as $module) {
            Module::create($module);
        }
    }
}
