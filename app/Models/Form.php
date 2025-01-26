<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    protected $fillable = ['title', 'module_id', 'statut'];

    // Relation : Un formulaire appartient à un module
    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    // Relation : Un formulaire a plusieurs questions
    public function questions()
    {
        return $this->hasMany(FormQuestion::class);
    }
}
