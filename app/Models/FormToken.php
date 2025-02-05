<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FormToken extends Model
{
    protected $fillable = ['form_id', 'student_id', 'token', 'used', 'expires_at'];

    protected $casts = [
        'expires_at' => 'datetime',
        'used' => 'boolean',
    ];

    public static function generateUniqueToken()
    {
        return Str::random(64);
    }

    public static function generateTokenForStudent(Student $student, Form $form)
    {
        // Génère un token unique basé sur l'email de l'étudiant et l'ID du formulaire
        return hash('sha256', $student->email . $form->id . Str::random(16));
    }

    public static function validateToken(string $token, Form $form)
    {
        // Recréer et valider le token pour l'étudiant
        $emailFromToken = substr($token, 0, strpos($token, '_'));
        $student = Student::where('email', $emailFromToken)->first();

        if (!$student) {
            return false;
        }

        $expectedToken = self::generateTokenForStudent($student, $form);
        return hash_equals($token, $expectedToken);
    }

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
