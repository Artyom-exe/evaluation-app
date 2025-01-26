<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\QuestionType;

class QuestionTypeSeeder extends Seeder
{
    public function run()
    {
        $types = [
            [
                'type' => 'text',
                'label' => 'Texte court',
                'icon' => 'ri-text'
            ],
            [
                'type' => 'textarea',
                'label' => 'Texte long',
                'icon' => 'ri-text-wrap'
            ],
            [
                'type' => 'radio',
                'label' => 'Choix unique',
                'icon' => 'ri-radio-button-line'
            ],
            [
                'type' => 'checkbox',
                'label' => 'Choix multiples',
                'icon' => 'ri-checkbox-multiple-line'
            ]
        ];

        foreach ($types as $type) {
            QuestionType::create($type);
        }
    }
}
