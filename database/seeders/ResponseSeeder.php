<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Response;

class ResponseSeeder extends Seeder
{
    public function run()
    {
        Response::create(['form_question_id' => 1, 'student_id' => 1, 'answers' => json_encode(['It was great!'])]);
        Response::create(['form_question_id' => 2, 'student_id' => 1, 'answers' => json_encode(['4'])]);
    }
}
