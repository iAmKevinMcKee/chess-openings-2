<?php

namespace App\Models;

use App\Models\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingSession extends Model
{
    use HasFactory, BelongsToUser;

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
