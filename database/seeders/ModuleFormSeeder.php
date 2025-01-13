<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ModuleForm;

class ModuleFormSeeder extends Seeder
{
    public function run()
    {
        ModuleForm::create(['module_id' => 1, 'form_id' => 1]);
        ModuleForm::create(['module_id' => 2, 'form_id' => 2]);
    }
}
