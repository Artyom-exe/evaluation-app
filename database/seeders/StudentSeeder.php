<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use Faker\Factory as Faker;

class StudentSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('fr_FR');

        // Créer 50 étudiants
        for ($i = 1; $i <= 50; $i++) {
            Student::create([
                'email' => $faker->unique()->email
            ]);
        }
    }
}
