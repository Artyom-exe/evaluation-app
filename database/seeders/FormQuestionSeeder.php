<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FormQuestion;
use App\Models\Form;
use App\Models\QuestionType;

class FormQuestionSeeder extends Seeder
{
    private $questionTemplates = [
        'text' => [
            'Que pensez-vous de ce cours en quelques mots ?',
            'Quel aspect du cours vous a le plus marqué ?',
            'Quelle amélioration suggérez-vous ?'
        ],
        'textarea' => [
            'Décrivez votre expérience globale dans ce cours.',
            'Quels sont les points forts et les points faibles du cours ?',
            'Donnez votre avis détaillé sur la pédagogie du professeur.'
        ],
        'radio' => [
            'Comment évaluez-vous la clarté des explications ?',
            'Quel est votre niveau de satisfaction global ?',
            'Comment jugez-vous la difficulté du cours ?'
        ],
        'checkbox' => [
            'Quels aspects du cours ont été les plus utiles ?',
            'Quels supports pédagogiques avez-vous appréciés ?',
            'Quels points pourraient être améliorés ?'
        ]
    ];

    public function run()
    {
        $forms = Form::all();
        $questionTypes = QuestionType::all()->keyBy('type');

        foreach ($forms as $form) {
            $order = 1;

            // Ajouter une question de chaque type
            foreach ($this->questionTemplates as $type => $questions) {
                FormQuestion::create([
                    'form_id' => $form->id,
                    'questions_types_id' => $questionTypes[$type]->id,
                    'label' => $questions[array_rand($questions)],
                    'order' => $order++
                ]);
            }

            // Ajouter 1-2 questions supplémentaires aléatoires
            for ($i = 0; $i < rand(1, 2); $i++) {
                $type = array_rand($this->questionTemplates);
                $questions = $this->questionTemplates[$type];

                FormQuestion::create([
                    'form_id' => $form->id,
                    'questions_types_id' => $questionTypes[$type]->id,
                    'label' => $questions[array_rand($questions)],
                    'order' => $order++
                ]);
            }
        }
    }
}
