<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CareerDomain extends Model
{
    protected $guarded = ['id'];

    public function careers()
    {
        return $this->hasMany(Career::class);
    }
}
