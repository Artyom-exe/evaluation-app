<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    protected $fillable = ['title', 'module_id', 'statut'];

    // Relation directe avec un module principal
    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    // Relation many-to-many avec les modules (pour des utilisations futures)
    public function additionalModules()
    {
        return $this->belongsToMany(Module::class, 'modules_forms')
            ->withTimestamps();
    }

    public function questions()
    {
        return $this->hasMany(FormQuestion::class);
    }
}
