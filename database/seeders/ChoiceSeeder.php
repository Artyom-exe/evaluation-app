<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Choice;

class ChoiceSeeder extends Seeder
{
    public function run()
    {
        $choices = [
            // Exemple de choix pour une question de type radio (satisfaction)
            [
                'form_question_id' => 1,
                'text' => 'Très satisfait'
            ],
            [
                'form_question_id' => 1,
                'text' => 'Satisfait'
            ],
            [
                'form_question_id' => 1,
                'text' => 'Neutre'
            ],
            [
                'form_question_id' => 1,
                'text' => 'Insatisfait'
            ],
            // Exemple de choix pour une autre question (checkbox)
            [
                'form_question_id' => 2,
                'text' => 'Support de cours'
            ],
            [
                'form_question_id' => 2,
                'text' => 'Exercices pratiques'
            ],
            [
                'form_question_id' => 2,
                'text' => 'Projets'
            ],
            [
                'form_question_id' => 2,
                'text' => 'Examens'
            ]
        ];

        foreach ($choices as $choice) {
            Choice::create($choice);
        }
    }
}
