<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_feedback_history_is_private_and_admin_response_notifies_owner(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $other = User::factory()->create(['role' => 'graduate']);
        $admin = User::factory()->create(['role' => 'admin']);
        $created = $this->actingAs($user, 'sanctum')->postJson('/api/feedback', ['type' => 'question', 'category' => 'Recommendations', 'subject' => 'How are matches calculated?', 'message' => 'Please explain how my saved careers affect future recommendations.'])->assertCreated();
        $id = $created->json('data.id');
        $this->actingAs($other, 'sanctum')->getJson('/api/feedback')->assertJsonPath('data.total', 0);
        $this->actingAs($admin, 'sanctum')->patchJson("/api/admin/feedback/{$id}", ['status' => 'resolved', 'priority' => 'normal', 'admin_response' => 'Your quiz, skills, interests, and saved activity are weighted transparently.'])->assertOk();
        $notice = $this->actingAs($user, 'sanctum')->getJson('/api/notifications')->assertOk()->assertJsonPath('meta.unread_count', 1);
        $noticeId = $notice->json('data.data.0.id');
        $this->actingAs($other, 'sanctum')->patchJson("/api/notifications/{$noticeId}/read")->assertNotFound();
        $this->actingAs($user, 'sanctum')->patchJson("/api/notifications/{$noticeId}/read")->assertOk();
    }

    public function test_announcement_targets_only_selected_active_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $graduate = User::factory()->create(['role' => 'graduate']);
        User::factory()->create(['role' => 'student', 'is_active' => false]);
        $this->actingAs($admin, 'sanctum')->postJson('/api/admin/announcements', ['title' => 'Student workshop', 'message' => 'A portfolio workshop is available this week.', 'target_role' => 'student', 'publish' => true])->assertCreated();
        $this->assertDatabaseCount('notifications', 1);
        $this->actingAs($student, 'sanctum')->getJson('/api/notifications')->assertJsonPath('meta.unread_count', 1);
        $this->actingAs($graduate, 'sanctum')->getJson('/api/notifications')->assertJsonPath('meta.unread_count', 0);
    }
}
