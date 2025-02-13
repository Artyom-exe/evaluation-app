<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Response extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_question_id',
        'student_id',
        'answers',
        'is_temporary'
    ];

    protected $casts = [
        'answers' => 'array'
    ];

    public function formQuestion()
    {
        return $this->belongsTo(FormQuestion::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
