<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Choice extends Model
{
    protected $fillable = ['text'];

    public function formQuestion()
    {
        return $this->belongsTo(FormQuestion::class);
    }
}
