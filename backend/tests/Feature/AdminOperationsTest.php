<?php

namespace Tests\Feature;

use App\Models\CareerDomain;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_uses_database_aggregates_and_rejects_regular_users(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $this->actingAs($student, 'sanctum')->getJson('/api/admin/dashboard')->assertForbidden();
        $this->actingAs($admin, 'sanctum')->getJson('/api/admin/dashboard')->assertOk()->assertJsonPath('data.kpis.users', 2)->assertJsonPath('data.users_by_role.admin', 1)->assertJsonPath('data.users_by_role.student', 1);
    }

    public function test_admin_can_deactivate_user_but_not_self(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['is_active' => true]);
        $this->actingAs($admin, 'sanctum')->patchJson("/api/admin/users/{$user->id}", ['is_active' => false])->assertOk()->assertJsonPath('data.is_active', false);
        $this->actingAs($admin, 'sanctum')->patchJson("/api/admin/users/{$admin->id}", ['is_active' => false])->assertUnprocessable();
    }

    public function test_admin_creates_career_with_normalized_skills(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $domain = CareerDomain::create(['name' => 'Technology', 'slug' => 'technology', 'description' => 'Digital systems']);
        $skill = Skill::create(['name' => 'Systems Thinking', 'slug' => 'systems-thinking']);
        $payload = ['career_domain_id' => $domain->id, 'title' => 'Platform Architect', 'summary' => 'Design resilient platforms.', 'description' => 'Lead architecture decisions across complex systems.', 'responsibilities' => ['Define system boundaries'], 'education_pathway' => ['Computing foundation', 'Architecture practice'], 'education_level' => 'Bachelor degree', 'experience_level' => 'Senior level', 'salary_min' => 70000, 'salary_max' => 120000, 'currency' => 'USD', 'salary_context' => 'Seeded demo range.', 'demand_level' => 'high', 'future_outlook' => 'Organizations continue modernizing core systems.', 'is_published' => true, 'skills' => [['id' => $skill->id, 'importance' => 'required', 'weight' => 9]]];
        $this->actingAs($admin, 'sanctum')->postJson('/api/admin/careers', $payload)->assertCreated()->assertJsonPath('data.title', 'Platform Architect');
        $this->assertDatabaseHas('career_skills', ['skill_id' => $skill->id, 'weight' => 9]);
    }
}
