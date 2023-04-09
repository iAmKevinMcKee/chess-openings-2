<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttemptMove extends Model
{
    use HasFactory;

    public function attempt()
    {
        return $this->belongsTo(Attempt::class);
    }
}
