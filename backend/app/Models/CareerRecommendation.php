<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CareerRecommendation extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['factors' => 'array', 'explanation' => 'array', 'overall_score' => 'float'];
    }

    public function career()
    {
        return $this->belongsTo(Career::class);
    }
}
