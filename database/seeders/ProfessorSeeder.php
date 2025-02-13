<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Professor;

class ProfessorSeeder extends Seeder
{
    public function run()
    {
        $professors = [
            ['name' => 'Dr. John Doe', 'email' => 'john.doe@univ.fr'],
            ['name' => 'Dr. Jane Smith', 'email' => 'jane.smith@univ.fr'],
            ['name' => 'Prof. Alice Johnson', 'email' => 'alice.johnson@univ.fr'],
            ['name' => 'Prof. Robert Wilson', 'email' => 'robert.wilson@univ.fr'],
            ['name' => 'Dr. Marie Dupont', 'email' => 'marie.dupont@univ.fr'],
        ];

        foreach ($professors as $professor) {
            Professor::create($professor);
        }
    }
}
