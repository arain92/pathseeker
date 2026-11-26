<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecentView extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['first_viewed_at' => 'datetime', 'last_viewed_at' => 'datetime'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function viewable()
    {
        return $this->morphTo();
    }
}
