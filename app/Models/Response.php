<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Response extends Model
{
    use HasFactory;

    protected $fillable = ['form_question_id', 'student_id', 'answers'];

    // Relation : Une réponse appartient à une question
    public function question()
    {
        return $this->belongsTo(FormQuestion::class, 'form_question_id');
    }

    // Relation : Une réponse appartient à un étudiant
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
