<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorrectMove extends Model
{
    use HasFactory;

    protected $casts = [
        'move' => 'array',
    ];

    public function opening()
    {
        return $this->belongsTo(Opening::class);
    }
}
