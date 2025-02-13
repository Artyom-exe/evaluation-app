<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'professor_id', 'year_id', 'image_path'];

    // Ajouter un attribut pour gérer l'image par défaut
    protected $defaultImageUrl = 'https://placehold.co/600x400/e2e8f0/475569?text=Module';

    public function getImagePathAttribute($value)
    {
        return $value ?: $this->defaultImageUrl;
    }

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
    public function forms(): HasMany
    {
        return $this->hasMany(Form::class);
    }

    // Relation : Un module peut avoir plusieurs étudiants via inscriptions
    public function students()
    {
        return $this->belongsToMany(Student::class, 'inscriptions');
    }
}
