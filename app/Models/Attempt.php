<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attempt extends Model
{
    use HasFactory;

    public function opening()
    {
        return $this->belongsTo(Opening::class);
    }
    public function attempt_moves()
    {
        return $this->hasMany(AttemptMove::class);
    }
}
