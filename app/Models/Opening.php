<?php

namespace App\Models;

use App\Models\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Opening extends Model
{
    use HasFactory, BelongsToUser;

    public function attempts()
    {
        return $this->hasMany(Attempt::class);
    }

    public function correctMoves()
    {
        return $this->hasMany(CorrectMove::class);
    }

    public function possibleMoves()
    {
        return $this->hasMany(PossibleMove::class);
    }

    public function firstMove()
    {
        return $this->hasOne(CorrectMove::class)->where('move_number', 1);
    }
}
