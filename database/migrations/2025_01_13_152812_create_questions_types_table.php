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
        Schema::create('questions_types', function (Blueprint $table) {
            $table->id();
            $table->string('type')->unique(); // Ex: text, radio, checkbox
            $table->string('label'); // Description of the type
            $table->string('icon'); // Ex: ri-text, ri-radio-button-line
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions_types');
    }
};
