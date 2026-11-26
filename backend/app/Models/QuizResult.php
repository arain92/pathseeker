<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizResult extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['dimension_scores' => 'array', 'strongest_traits' => 'array', 'recommended_domains' => 'array'];
    }
}
