<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_question_id')->constrained('form_questions')->onDelete('cascade');
            $table->foreignId('student_id')->nullable()->constrained()->onDelete('cascade');
            $table->json('answers')->nullable();
            $table->boolean('is_temporary')->default(false); // Ajout du champ directement
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('responses');
    }
};
