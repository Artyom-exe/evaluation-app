<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;

class StudentSeeder extends Seeder
{
    public function run()
    {
        Student::create(['email' => 'student1@example.com']);
        Student::create(['email' => 'student2@example.com']);
    }
}
