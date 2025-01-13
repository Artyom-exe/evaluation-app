<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->call([
            YearSeeder::class,           // Les années doivent être créées en premier
            ProfessorSeeder::class,      // Les professeurs doivent exister avant les modules
            ModuleSeeder::class,         // Les modules dépendent des années et des professeurs
            StudentSeeder::class,        // Les étudiants n'ont pas de dépendances
            QuestionTypeSeeder::class,   // Les types de questions doivent exister avant les questions
            FormSeeder::class,           // Les formulaires dépendent des modules
            FormQuestionSeeder::class,   // Les questions dépendent des formulaires et des types de questions
            ChoiceSeeder::class,         // Les choix dépendent des questions
            InscriptionSeeder::class,    // Les inscriptions dépendent des étudiants et des modules
            ModuleFormSeeder::class,     // Les liens entre modules et formulaires dépendent des modules et formulaires
            ResponseSeeder::class,       // Les réponses dépendent des questions et des étudiants
        ]);
    }
}
