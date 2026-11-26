<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'completed_at' => 'datetime'];
    }

    public function answers()
    {
        return $this->hasMany(QuizAnswer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function result()
    {
        return $this->hasOne(QuizResult::class);
    }

    public function recommendations()
    {
        return $this->hasMany(CareerRecommendation::class)->orderBy('rank');
    }
}
