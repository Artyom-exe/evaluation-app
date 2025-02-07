<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormAccessToken extends Model
{
    protected $fillable = [
        'student_id',
        'form_id',
        'token',
        'expires_at',
        'used'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function form()
    {
        return $this->belongsTo(Form::class);
    }
}
