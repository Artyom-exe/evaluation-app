<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Choice;

class ChoiceSeeder extends Seeder
{
    public function run()
    {
        Choice::create(['form_question_id' => 2, 'value' => '1', 'label' => '1']);
        Choice::create(['form_question_id' => 2, 'value' => '2', 'label' => '2']);
        Choice::create(['form_question_id' => 2, 'value' => '3', 'label' => '3']);
    }
}
