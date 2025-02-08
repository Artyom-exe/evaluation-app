<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Inscription;
use App\Models\Student;
use App\Models\Module;

class InscriptionSeeder extends Seeder
{
    public function run()
    {
        $students = Student::all();
        $modules = Module::all();

        // Inscrire chaque étudiant à 3-5 modules aléatoires
        foreach ($students as $student) {
            $randomModules = $modules->random(rand(3, 5));
            foreach ($randomModules as $module) {
                $student->modules()->attach($module->id);
            }
        }
    }
}
