<?php

namespace Tests\Feature;

use App\Models\Career;
use App\Models\CareerDomain;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_bookmark_is_idempotent_and_note_is_private_to_its_owner(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $career = $this->career();

        $created = $this->actingAs($owner, 'sanctum')->postJson('/api/bookmarks', ['type' => 'career', 'id' => $career->id, 'note' => 'Build a portfolio project.'])->assertCreated();
        $this->actingAs($owner, 'sanctum')->postJson('/api/bookmarks', ['type' => 'career', 'id' => $career->id])->assertOk();
        $this->assertDatabaseCount('bookmarks', 1);

        $bookmarkId = $created->json('data.id');
        $this->actingAs($other, 'sanctum')->patchJson("/api/bookmarks/{$bookmarkId}", ['note' => 'Trying to replace it'])->assertNotFound();
        $this->actingAs($other, 'sanctum')->deleteJson("/api/bookmarks/{$bookmarkId}")->assertNotFound();
        $this->actingAs($owner, 'sanctum')->getJson('/api/bookmarks')->assertOk()->assertJsonPath('data.0.note', 'Build a portfolio project.');
    }

    public function test_recent_view_upserts_and_dashboard_uses_real_engagement(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $user->profile()->create();
        $career = $this->career();

        $this->actingAs($user, 'sanctum')->postJson('/api/recent-views', ['type' => 'career', 'id' => $career->id])->assertCreated();
        $this->actingAs($user, 'sanctum')->postJson('/api/recent-views', ['type' => 'career', 'id' => $career->id])->assertOk()->assertJsonPath('data.view_count', 2);

        $this->assertDatabaseCount('recent_views', 1);
        $this->actingAs($user, 'sanctum')->getJson('/api/dashboard')->assertOk()
            ->assertJsonPath('data.recent.0.career.title', 'Data Engineer')
            ->assertJsonPath('data.trending.0.engagement_count', 2);
    }

    public function test_personalization_requires_authentication_and_rejects_unpublished_content(): void
    {
        $career = $this->career(false);
        $this->getJson('/api/bookmarks')->assertUnauthorized();
        $this->actingAs(User::factory()->create(), 'sanctum')->postJson('/api/bookmarks', ['type' => 'career', 'id' => $career->id])->assertNotFound();
    }

    private function career(bool $published = true): Career
    {
        $domain = CareerDomain::create(['name' => 'Technology', 'slug' => 'technology', 'description' => 'Digital systems']);

        return Career::create(['career_domain_id' => $domain->id, 'title' => 'Data Engineer', 'slug' => 'data-engineer', 'summary' => 'Design reliable data systems.', 'description' => 'Build data platforms.', 'responsibilities' => ['Design pipelines'], 'education_pathway' => ['Foundation'], 'education_level' => 'Bachelor degree', 'experience_level' => 'Entry level', 'salary_min' => 30000, 'salary_max' => 50000, 'currency' => 'USD', 'salary_context' => 'Seeded demo range.', 'demand_level' => 'high', 'is_published' => $published]);
    }
}
