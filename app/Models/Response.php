<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Response extends Model
{
    protected $fillable = ['form_question_id', 'student_id', 'answers'];

    protected $casts = [
        'answers' => 'array'
    ];

    public function form_question()
    {
        return $this->belongsTo(FormQuestion::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
