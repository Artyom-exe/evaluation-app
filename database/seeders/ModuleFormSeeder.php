<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Form;
use App\Models\Module;

class ModuleFormSeeder extends Seeder
{
    public function run()
    {
        // Récupérer tous les forms existants
        $forms = Form::all();

        foreach ($forms as $form) {
            DB::table('modules_forms')->insert([
                'module_id' => $form->module_id,
                'form_id' => $form->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
