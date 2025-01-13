<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = ['email'];

    // Relation : Un étudiant peut avoir plusieurs inscriptions
    public function modules()
    {
        return $this->belongsToMany(Module::class, 'inscriptions');
    }

    // Relation : Un étudiant peut soumettre plusieurs réponses
    public function responses()
    {
        return $this->hasMany(Response::class);
    }
}
