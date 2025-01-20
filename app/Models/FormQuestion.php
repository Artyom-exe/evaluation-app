<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormQuestion extends Model
{
    protected $fillable = ['label', 'questions_types_id'];

    // Relation : Une question appartient à un formulaire
    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    // Relation : Une question a un type
    public function questionType()
    {
        return $this->belongsTo(QuestionType::class, 'questions_types_id');
    }

    // Relation : Une question peut avoir plusieurs choix
    public function choices()
    {
        return $this->hasMany(Choice::class);
    }
}
