<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizAnswer extends Model
{
    protected $guarded = ['id'];

    public function option()
    {
        return $this->belongsTo(QuizOption::class, 'quiz_option_id');
    }
}
