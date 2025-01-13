<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Choice extends Model
{
    use HasFactory;

    protected $fillable = ['form_question_id', 'value', 'label', 'order'];

    // Relation : Un choix appartient à une question
    public function question()
    {
        return $this->belongsTo(FormQuestion::class);
    }
}
