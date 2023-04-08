<?php

namespace App\Models;

use App\Models\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorrectMove extends Model
{
    use HasFactory, BelongsToUser;

    protected $casts = [
        'move' => 'array',
    ];

    public function opening()
    {
        return $this->belongsTo(Opening::class);
    }

    public function possibleMoves()
    {
        return $this->hasMany(PossibleMove::class, 'from_fen', 'to_fen');
    }
}
