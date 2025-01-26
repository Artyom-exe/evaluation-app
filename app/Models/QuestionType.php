<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionType extends Model
{
    use HasFactory;

    protected $fillable = ['type', 'label', 'icon'];
    protected $table = 'questions_types';


    // Relation : Un type de question peut être associé à plusieurs questions
    public function questions()
    {
        return $this->hasMany(FormQuestion::class, 'questions_types_id');
    }
}
