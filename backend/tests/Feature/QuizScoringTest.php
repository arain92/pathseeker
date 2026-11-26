<?php

namespace Tests\Feature;

use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizScoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_attempt_creates_transparent_result_snapshot(): void
    {
        $this->seed();
        $user = User::where('role', 'student')->firstOrFail();
        $attemptId = $this->actingAs($user, 'sanctum')->postJson('/api/quiz/attempts')->assertCreated()->json('data.id');

        foreach (QuizQuestion::with('options')->where('is_active', true)->get() as $question) {
            $this->actingAs($user, 'sanctum')->putJson("/api/quiz/attempts/{$attemptId}/answer", [
                'question_id' => $question->id,
                'option_id' => $question->options->last()->id,
            ])->assertOk();
        }

        $response = $this->actingAs($user, 'sanctum')->postJson("/api/quiz/attempts/{$attemptId}/complete");
        $response->assertOk()
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.result.scoring_version', 'v1')
            ->assertJsonCount(10, 'data.recommendations')
            ->assertJsonPath('data.recommendations.0.factors.interaction_signal', 0);
        $this->assertDatabaseCount('quiz_results', 1);
        $this->assertDatabaseCount('career_recommendations', 10);
    }

    public function test_incomplete_and_foreign_attempts_are_rejected(): void
    {
        $this->seed();
        $owner = User::where('role', 'student')->firstOrFail();
        $other = User::where('role', 'graduate')->firstOrFail();
        $attempt = QuizAttempt::create(['user_id' => $owner->id, 'status' => 'in_progress', 'started_at' => now()]);

        $this->actingAs($owner, 'sanctum')->postJson("/api/quiz/attempts/{$attempt->id}/complete")->assertUnprocessable();
        $this->actingAs($other, 'sanctum')->getJson("/api/quiz/attempts/{$attempt->id}/result")->assertForbidden();
    }
}
