<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FormQuestion;

class FormQuestionSeeder extends Seeder
{
    public function run()
    {
        FormQuestion::create(['form_id' => 1, 'questions_types_id' => 1, 'label' => 'How was the course?', 'order' => 1]);
        FormQuestion::create(['form_id' => 1, 'questions_types_id' => 2, 'label' => 'Rate the professor (1-5)', 'order' => 2]);
    }
}
