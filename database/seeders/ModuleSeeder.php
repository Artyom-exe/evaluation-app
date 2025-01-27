<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;
use Illuminate\Support\Facades\Storage;

class ModuleSeeder extends Seeder
{
    public function run()
    {
        $defaultImageUrl = 'https://placehold.co/600x400/e2e8f0/475569?text=Module';

        $modules = [
            [
                'name' => 'Mathematics',
                'description' => 'Advanced Math Course',
                'professor_id' => 1,
                'year_id' => 1,
                'image_path' => $defaultImageUrl
            ],
            [
                'name' => 'Physics',
                'description' => 'Intro to Physics',
                'professor_id' => 2,
                'year_id' => 1,
                'image_path' => $defaultImageUrl
            ],
            [
                'name' => 'Computer Science',
                'description' => 'Programming Fundamentals',
                'professor_id' => 1,
                'year_id' => 2,
                'image_path' => $defaultImageUrl
            ]
        ];

        foreach ($modules as $module) {
            Module::create($module);
        }
    }
}
