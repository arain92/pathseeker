<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Career extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['responsibilities' => 'array', 'education_pathway' => 'array', 'is_published' => 'boolean'];
    }

    public function domain()
    {
        return $this->belongsTo(CareerDomain::class, 'career_domain_id');
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'career_skills')->withPivot(['importance', 'weight']);
    }

    public function interests()
    {
        return $this->belongsToMany(Interest::class, 'career_interest_weights')->withPivot('weight');
    }

    public function bookmarks()
    {
        return $this->morphMany(Bookmark::class, 'bookmarkable');
    }

    public function recentViews()
    {
        return $this->morphMany(RecentView::class, 'viewable');
    }

    public function multimedia()
    {
        return $this->belongsToMany(Multimedia::class, 'career_multimedia');
    }

    public function recommendations()
    {
        return $this->hasMany(CareerRecommendation::class);
    }
}
