<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $guarded = ['id'];

    public function resources()
    {
        return $this->belongsToMany(Resource::class);
    }

    public function multimedia()
    {
        return $this->belongsToMany(Multimedia::class);
    }
}
