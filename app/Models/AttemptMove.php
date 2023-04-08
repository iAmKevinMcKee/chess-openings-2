<?php

namespace App\Models;

use App\Models\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttemptMove extends Model
{
    use HasFactory, BelongsToUser;

    public function attempt()
    {
        return $this->belongsTo(Attempt::class);
    }
}
