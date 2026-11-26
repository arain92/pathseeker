<?php

namespace Tests\Feature;

use App\Models\Interest;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_only_their_career_passport(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $user->profile()->create();
        $skills = collect(['Communication', 'Research', 'Problem Solving'])->map(fn ($name) => Skill::create(['name' => $name, 'slug' => str($name)->slug()]));
        $interests = collect(['Scientific Discovery', 'Teaching'])->map(fn ($name) => Interest::create(['name' => $name, 'slug' => str($name)->slug()]));

        $response = $this->actingAs($user, 'sanctum')->putJson('/api/profile', [
            'name' => 'Areeba Khan',
            'country' => 'Pakistan',
            'role' => 'graduate',
            'education_level' => 'Undergraduate',
            'degree' => 'BS Computer Science',
            'field_of_study' => 'Computer Science',
            'graduation_year' => 2026,
            'career_goal' => 'Find a technology path combining research and practical problem solving.',
            'skills' => $skills->map(fn ($skill) => ['id' => $skill->id, 'proficiency' => 3])->all(),
            'interest_ids' => $interests->pluck('id')->all(),
        ]);

        $this->assertDatabaseHas('users', ['id' => $user->id, 'role' => 'graduate']);
        $response->assertOk()->assertJsonPath('data.completion.percentage', 100);
        $this->assertDatabaseHas('user_profiles', ['user_id' => $user->id, 'country' => 'Pakistan']);
        $this->assertDatabaseCount('user_skills', 3);
        $this->assertDatabaseCount('user_interests', 2);
    }

    public function test_profile_requires_authentication(): void
    {
        $this->getJson('/api/profile')->assertUnauthorized();
    }
}
