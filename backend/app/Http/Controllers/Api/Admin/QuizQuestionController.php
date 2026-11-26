<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuizQuestion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuizQuestionController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Quiz questions retrieved.', 'data' => QuizQuestion::with('options')->orderBy('sort_order')->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $item = DB::transaction(function () use ($request) {
            $data = $this->validated($request);
            $options = $data['options'];
            unset($data['options']);
            $item = QuizQuestion::create($data);
            $this->options($item, $options);

            return $item;
        });

        return response()->json(['success' => true, 'message' => 'Quiz question created.', 'data' => $item->load('options')], 201);
    }

    public function show(QuizQuestion $quizQuestion): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Quiz question retrieved.', 'data' => $quizQuestion->load('options')]);
    }

    public function update(Request $request, QuizQuestion $quizQuestion): JsonResponse
    {
        abort_if($quizQuestion->answers()->exists(), 409, 'Questions with assessment history cannot have scoring options replaced. Deactivate and create a new version.');
        DB::transaction(function () use ($request, $quizQuestion) {
            $data = $this->validated($request);
            $options = $data['options'];
            unset($data['options']);
            $quizQuestion->update($data);
            $quizQuestion->options()->delete();
            $this->options($quizQuestion, $options);
        });

        return response()->json(['success' => true, 'message' => 'Quiz question updated.', 'data' => $quizQuestion->fresh()->load('options')]);
    }

    public function destroy(QuizQuestion $quizQuestion): JsonResponse
    {
        if ($quizQuestion->answers()->exists()) {
            $quizQuestion->update(['is_active' => false]);

            return response()->json(['success' => true, 'message' => 'Question has assessment history and was deactivated instead.', 'data' => $quizQuestion]);
        }$quizQuestion->delete();

        return response()->json(['success' => true, 'message' => 'Quiz question deleted.', 'data' => null]);
    }

    private function validated(Request $request): array
    {
        return $request->validate(['prompt' => 'required|string|max:1000', 'section' => 'required|string|max:60', 'type' => 'required|in:likert,multiple_choice,scenario,slider', 'sort_order' => 'required|integer|min:0', 'is_active' => 'required|boolean', 'options' => 'required|array|min:2', 'options.*.label' => 'required|string|max:255', 'options.*.value' => 'required|string|max:80', 'options.*.dimension_weights' => 'required|array|min:1', 'options.*.dimension_weights.*' => 'numeric|between:-1,1', 'options.*.sort_order' => 'required|integer|min:0']);
    }

    private function options(QuizQuestion $item, array $options): void
    {
        $item->options()->createMany($options);
    }
}
