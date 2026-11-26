<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuizAttempt;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Services\CareerMatchingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function questions(): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Assessment questions retrieved.', 'data' => QuizQuestion::query()->with('options')->where('is_active', true)->orderBy('sort_order')->get()]);
    }

    public function start(Request $request): JsonResponse
    {
        $attempt = $request->user()->quizAttempts()->where('status', 'in_progress')->latest()->first();
        $created = false;
        if (! $attempt) {
            $attempt = $request->user()->quizAttempts()->create(['status' => 'in_progress', 'started_at' => now()]);
            $created = true;
        }

        return response()->json(['success' => true, 'message' => $created ? 'Assessment started.' : 'Assessment resumed.', 'data' => $attempt->load('answers')], $created ? 201 : 200);
    }

    public function answer(Request $request, QuizAttempt $attempt): JsonResponse
    {
        $this->own($request, $attempt);
        abort_if($attempt->status === 'completed', 409, 'Completed assessments cannot be changed.');
        $data = $request->validate(['question_id' => ['required', 'integer', 'exists:quiz_questions,id'], 'option_id' => ['required', 'integer', 'exists:quiz_options,id']]);
        $valid = QuizOption::query()->whereKey($data['option_id'])->where('quiz_question_id', $data['question_id'])->exists();
        abort_unless($valid, 422, 'The selected option does not belong to this question.');
        $attempt->answers()->updateOrCreate(['quiz_question_id' => $data['question_id']], ['quiz_option_id' => $data['option_id']]);

        return response()->json(['success' => true, 'message' => 'Answer saved.', 'data' => null]);
    }

    public function complete(Request $request, QuizAttempt $attempt, CareerMatchingService $matching): JsonResponse
    {
        $this->own($request, $attempt);
        abort_if($attempt->status === 'completed', 409, 'Assessment already completed.');
        $required = QuizQuestion::where('is_active', true)->count();
        abort_if($attempt->answers()->count() !== $required, 422, 'Please answer every question before completing the assessment.');

        return response()->json(['success' => true, 'message' => 'Your Career DNA report is ready.', 'data' => $matching->complete($attempt)]);
    }

    public function result(Request $request, QuizAttempt $attempt): JsonResponse
    {
        $this->own($request, $attempt);
        abort_unless($attempt->status === 'completed', 404);

        return response()->json(['success' => true, 'message' => 'Assessment result retrieved.', 'data' => $attempt->load(['result', 'recommendations.career.domain', 'recommendations.career.skills'])]);
    }

    public function history(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Assessment history retrieved.', 'data' => $request->user()->quizAttempts()->where('status', 'completed')->with('result')->latest('completed_at')->paginate(10)]);
    }

    private function own(Request $request, QuizAttempt $attempt): void
    {
        abort_unless($attempt->user_id === $request->user()->id, 403);
    }
}
