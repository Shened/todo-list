<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function smallSteps()
    {
        return $this->hasMany(SmallSteps::class);
    }

    public function checkCompletion()
    {
        return $this->smallSteps->every(fn($step) => $step->completed);
    }
}
