<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaRating extends Model
{
    protected $guarded = ['id'];

    public function multimedia()
    {
        return $this->belongsTo(Multimedia::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
