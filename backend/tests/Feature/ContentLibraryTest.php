<?php

namespace Tests\Feature;

use App\Models\Multimedia;
use App\Models\Resource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentLibraryTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_published_content_is_public_and_downloads_increment(): void
    {
        $live = Resource::create(['title' => 'Career Canvas', 'slug' => 'career-canvas', 'description' => 'Plan decisions', 'type' => 'guide', 'audience' => 'everyone', 'category' => 'Planning', 'file_url' => 'https://example.test/canvas', 'is_published' => true]);
        Resource::create(['title' => 'Draft', 'slug' => 'draft', 'description' => 'Draft', 'type' => 'guide', 'audience' => 'everyone', 'category' => 'Planning', 'file_url' => 'https://example.test/draft', 'is_published' => false]);
        $this->getJson('/api/resources')->assertOk()->assertJsonPath('meta.total', 1);
        $this->postJson('/api/resources/'.$live->slug.'/download')->assertOk()->assertJsonPath('data.download_count', 1);
    }

    public function test_media_rating_is_one_updateable_rating_per_user(): void
    {
        $user = User::factory()->create();
        $media = Multimedia::create(['title' => 'Career Map', 'slug' => 'career-map', 'description' => 'A guide', 'type' => 'video', 'source_url' => 'https://example.test', 'category' => 'Planning', 'is_published' => true]);
        $this->actingAs($user, 'sanctum')->postJson("/api/multimedia/{$media->id}/rating", ['rating' => 4])->assertOk()->assertJsonPath('data.average', 4);
        $this->actingAs($user, 'sanctum')->postJson("/api/multimedia/{$media->id}/rating", ['rating' => 5])->assertOk()->assertJsonPath('data.average', 5);
        $this->assertDatabaseCount('media_ratings', 1);
    }

    public function test_only_published_content_and_approved_stories_can_be_bookmarked(): void
    {
        $user = User::factory()->create();
        $published = Resource::create(['title' => 'Live Guide', 'slug' => 'live-guide', 'description' => 'Useful guide', 'type' => 'guide', 'audience' => 'everyone', 'category' => 'Planning', 'file_url' => 'https://example.test/live', 'is_published' => true]);
        $draft = Resource::create(['title' => 'Draft Guide', 'slug' => 'draft-guide', 'description' => 'Draft guide', 'type' => 'guide', 'audience' => 'everyone', 'category' => 'Planning', 'file_url' => 'https://example.test/draft', 'is_published' => false]);

        $this->actingAs($user, 'sanctum')->postJson('/api/bookmarks', ['type' => 'resource', 'id' => $published->id])->assertCreated()->assertJsonPath('data.type', 'resource');
        $this->actingAs($user, 'sanctum')->postJson('/api/bookmarks', ['type' => 'resource', 'id' => $draft->id])->assertNotFound();
        $this->actingAs($user, 'sanctum')->getJson('/api/bookmarks?type=resource')->assertOk()->assertJsonPath('data.0.item.title', 'Live Guide');
    }

    public function test_story_submission_requires_review_and_only_admin_can_moderate(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($user, 'sanctum')->postJson('/api/success-story-submissions', ['person_name' => 'Ayesha', 'career_title' => 'Analyst', 'domain' => 'Business', 'summary' => 'I tested my direction with projects and mentors.', 'timeline' => ['Start', 'Education', 'Challenge', 'Turning point', 'Outcome'], 'advice' => 'Run small experiments before committing.'])->assertCreated()->assertJsonPath('data.status', 'pending');
        $id = $response->json('data.id');
        $this->getJson('/api/success-stories')->assertJsonPath('meta.total', 0);
        $this->actingAs($user, 'sanctum')->patchJson("/api/admin/success-stories/{$id}", ['status' => 'approved'])->assertForbidden();
        $this->actingAs($admin, 'sanctum')->patchJson("/api/admin/success-stories/{$id}", ['status' => 'approved'])->assertOk();
        $this->getJson('/api/success-stories')->assertJsonPath('meta.total', 1);
    }
}
