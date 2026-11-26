<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'profile' => $this->profile,
            'skills' => $this->skills->map(fn ($skill) => [
                'id' => $skill->id, 'name' => $skill->name, 'proficiency' => $skill->pivot->proficiency,
            ]),
            'interests' => $this->interests->map(fn ($interest) => ['id' => $interest->id, 'name' => $interest->name]),
            'completion' => $this->additional['completion'] ?? null,
        ];
    }
}
