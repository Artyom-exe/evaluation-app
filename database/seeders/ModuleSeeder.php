<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;
use Illuminate\Support\Facades\Storage;

class ModuleSeeder extends Seeder
{
    public function run()
    {

        $modules = [
            [
                'name' => 'Mathematics',
                'professor_id' => 1,
                'year_id' => 1,

            ],
            [
                'name' => 'Physics',
                'professor_id' => 2,
                'year_id' => 1,
            ],
            [
                'name' => 'Computer Science',
                'professor_id' => 1,
                'year_id' => 2,
            ]
        ];

        foreach ($modules as $module) {
            Module::create($module);
        }
    }
}
