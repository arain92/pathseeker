<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CareerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id, 'title' => $this->title, 'slug' => $this->slug, 'summary' => $this->summary,
            'description' => $this->when($request->routeIs('careers.show'), $this->description),
            'domain' => ['id' => $this->domain->id, 'name' => $this->domain->name, 'slug' => $this->domain->slug],
            'education_level' => $this->education_level, 'experience_level' => $this->experience_level,
            'salary' => ['minimum' => $this->salary_min, 'maximum' => $this->salary_max, 'currency' => $this->currency, 'context' => $this->salary_context],
            'demand_level' => $this->demand_level, 'future_outlook' => $this->future_outlook, 'icon' => $this->icon,
            'responsibilities' => $this->when($request->routeIs('careers.show'), $this->responsibilities),
            'education_pathway' => $this->when($request->routeIs('careers.show'), $this->education_pathway),
            'skills' => $this->whenLoaded('skills', fn () => $this->skills->map(fn ($skill) => ['id' => $skill->id, 'name' => $skill->name, 'importance' => $skill->pivot->importance, 'weight' => $skill->pivot->weight])),
        ];
    }
}
