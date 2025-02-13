<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Response;
use App\Models\FormQuestion;
use App\Models\Student;
use App\Models\Form;

class ResponseSeeder extends Seeder
{
    public function run()
    {
        $forms = Form::where('statut', 'completed')->get();

        foreach ($forms as $form) {
            $students = $form->module->students;
            $questions = $form->questions;

            foreach ($students as $student) {
                foreach ($questions as $question) {
                    $answer = $this->generateAnswer($question);

                    Response::create([
                        'form_question_id' => $question->id,
                        'student_id' => $student->id,
                        'answers' => ['value' => $answer],
                        'is_temporary' => false
                    ]);
                }
            }
        }
    }

    private function generateAnswer($question)
    {
        $type = $question->questionType->type;

        switch ($type) {
            case 'text':
                return 'Très bon cours, les explications étaient claires.';

            case 'textarea':
                return 'Le cours était très intéressant et bien structuré. Les exemples pratiques ont été particulièrement utiles pour comprendre les concepts.';

            case 'radio':
                $choices = $question->choices;
                return $choices->random()->text;

            case 'checkbox':
                $choices = $question->choices;
                $selectedChoices = $choices->random(rand(1, 3));
                return $selectedChoices->pluck('text')->toArray();

            default:
                return 'Réponse par défaut';
        }
    }
}
