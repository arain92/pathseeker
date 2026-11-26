<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuccessStory extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['timeline' => 'array', 'published_at' => 'datetime'];
    }

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}
