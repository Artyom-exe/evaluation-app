<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormQuestion extends Model
{
    use HasFactory;

    protected $fillable = ['form_id', 'questions_types_id', 'label', 'order'];

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

    // Relation : Une question peut recevoir plusieurs réponses
    public function responses()
    {
        return $this->hasMany(Response::class);
    }
}
