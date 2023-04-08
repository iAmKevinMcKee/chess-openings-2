<?php

namespace App\Models;

use App\Models\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PossibleMove extends Model
{
    use HasFactory, BelongsToUser;

    public function correctMove()
    {
        return $this->belongsTo(CorrectMove::class, 'to_fen', 'from_fen')->where('opening_id', $this->opening_id);
    }
}
