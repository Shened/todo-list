<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmallSteps extends Model
{
    use HasFactory;

    protected $fillable = ['todo_id', 'name', 'description', 'completed'];

    public function todo()
    {
        return $this->belongsTo(Todo::class);
    }
}
