<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingSession extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function totalAttempts()
    {
        return $this->correct + $this->incorrect;
    }

    public function percentCorrect()
    {
        if($this->totalAttempts() > 0) {
            return ($this->correct / $this->totalAttempts()) * 100;
        }
        return 100;
    }
}
