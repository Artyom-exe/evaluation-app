<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Choice;
use App\Models\FormQuestion;

class ChoiceSeeder extends Seeder
{
    public function run()
    {
        $choicesByType = [
            'radio' => [
                'satisfaction' => [
                    'Très satisfait',
                    'Satisfait',
                    'Neutre',
                    'Peu satisfait',
                    'Pas du tout satisfait'
                ],
                'difficulté' => [
                    'Très facile',
                    'Facile',
                    'Moyen',
                    'Difficile',
                    'Très difficile'
                ],
                'clarté' => [
                    'Très clair',
                    'Clair',
                    'Moyen',
                    'Peu clair',
                    'Pas du tout clair'
                ]
            ],
            'checkbox' => [
                'supports' => [
                    'Support de cours',
                    'Présentations',
                    'Exercices pratiques',
                    'Travaux dirigés',
                    'Projets',
                    'Examens blancs'
                ],
                'amélioration' => [
                    'Plus d\'exercices',
                    'Plus de projets',
                    'Plus d\'exemples concrets',
                    'Plus de temps de pratique',
                    'Plus d\'interactions'
                ],
                'points_forts' => [
                    'Qualité des explications',
                    'Disponibilité du professeur',
                    'Support de cours',
                    'Exercices pratiques',
                    'Ambiance de travail'
                ]
            ]
        ];

        // Récupérer toutes les questions de type radio ou checkbox
        $questions = FormQuestion::whereHas('questionType', function ($query) {
            $query->whereIn('type', ['radio', 'checkbox']);
        })->get();

        foreach ($questions as $question) {
            $type = $question->questionType->type;
            $choices = $choicesByType[$type][array_rand($choicesByType[$type])];

            foreach ($choices as $choiceText) {
                Choice::create([
                    'form_question_id' => $question->id,
                    'text' => $choiceText
                ]);
            }
        }
    }
}
