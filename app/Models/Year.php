<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Year extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    // Relation : Une année a plusieurs modules
    public function modules()
    {
        return $this->hasMany(Module::class);
    }
}
