<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    use HasFactory;

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
