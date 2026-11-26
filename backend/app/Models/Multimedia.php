<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Multimedia extends Model
{
    protected $table = 'multimedia';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'multimedia_tag');
    }

    public function ratings()
    {
        return $this->hasMany(MediaRating::class);
    }

    public function careers()
    {
        return $this->belongsToMany(Career::class, 'career_multimedia');
    }

    public function bookmarks()
    {
        return $this->morphMany(Bookmark::class, 'bookmarkable');
    }
}
