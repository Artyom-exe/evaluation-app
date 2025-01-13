<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'professor_id', 'year_id'];

    // Relation : Un module appartient à un professeur
    public function professor()
    {
        return $this->belongsTo(Professor::class);
    }

    // Relation : Un module appartient à une année
    public function year()
    {
        return $this->belongsTo(Year::class);
    }

    // Relation : Un module peut avoir plusieurs formulaires
    public function forms()
    {
        return $this->belongsToMany(Form::class, 'modules_forms');
    }

    // Relation : Un module peut avoir plusieurs étudiants via inscriptions
    public function students()
    {
        return $this->belongsToMany(Student::class, 'inscriptions');
    }
}
