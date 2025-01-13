<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\QuestionType;

class QuestionTypeSeeder extends Seeder
{
    public function run()
    {
        QuestionType::create(['type' => 'text', 'label' => 'Open-ended question']);
        QuestionType::create(['type' => 'radio', 'label' => 'Single choice']);
    }
}
