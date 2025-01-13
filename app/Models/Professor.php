<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Professor extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email'];

    // Relation : Un professeur peut avoir plusieurs modules
    public function modules()
    {
        return $this->hasMany(Module::class);
    }
}
