<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_id',
        'student_id',
        'answers'
    ];

    protected $casts = [
        'answers' => 'array'
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
