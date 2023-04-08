<?php

namespace App\Models;

use App\Models\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attempt extends Model
{
    use HasFactory, BelongsToUser;

    public function opening()
    {
        return $this->belongsTo(Opening::class);
    }
    public function attempt_moves()
    {
        return $this->hasMany(AttemptMove::class);
    }
}
