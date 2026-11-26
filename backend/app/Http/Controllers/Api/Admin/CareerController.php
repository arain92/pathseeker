<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Career;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CareerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $f = $request->validate(['search' => 'nullable|string|max:100', 'published' => 'nullable|boolean']);
        $items = Career::with('domain')->withCount(['skills', 'bookmarks'])->when($f['search'] ?? null, fn ($q, $v) => $q->where('title', 'like', "%{$v}%"))->when(array_key_exists('published', $f), fn ($q) => $q->where('is_published', $f['published']))->latest()->paginate(20);

        return response()->json(['success' => true, 'message' => 'Admin careers retrieved.', 'data' => $items]);
    }

    public function store(Request $request): JsonResponse
    {
        $career = DB::transaction(function () use ($request) {
            $data = $this->validated($request);
            $skills = $data['skills'];
            unset($data['skills']);
            $data['slug'] = str($data['title'])->slug();
            $career = Career::create($data);
            $this->syncSkills($career, $skills);

            return $career;
        });

        return response()->json(['success' => true, 'message' => 'Career created.', 'data' => $career->load(['domain', 'skills'])], 201);
    }

    public function show(Career $career): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Admin career retrieved.', 'data' => $career->load(['domain', 'skills', 'interests'])]);
    }

    public function update(Request $request, Career $career): JsonResponse
    {
        DB::transaction(function () use ($request, $career) {
            $data = $this->validated($request, $career);
            $skills = $data['skills'];
            unset($data['skills']);
            $data['slug'] = str($data['title'])->slug();
            $career->update($data);
            $this->syncSkills($career, $skills);
        });

        return response()->json(['success' => true, 'message' => 'Career updated.', 'data' => $career->fresh()->load(['domain', 'skills'])]);
    }

    public function destroy(Career $career): JsonResponse
    {
        abort_if($career->bookmarks()->exists() || $career->recommendations()->exists(), 409, 'Unpublish careers with user history instead of deleting them.');
        $career->delete();

        return response()->json(['success' => true, 'message' => 'Career deleted.', 'data' => null]);
    }

    private function validated(Request $request, ?Career $career = null): array
    {
        return $request->validate(['career_domain_id' => 'required|exists:career_domains,id', 'title' => ['required', 'string', 'max:150', Rule::unique('careers', 'title')->ignore($career?->id)], 'summary' => 'required|string|max:1000', 'description' => 'required|string|max:10000', 'responsibilities' => 'required|array|min:1', 'responsibilities.*' => 'required|string|max:500', 'education_pathway' => 'required|array|min:1', 'education_pathway.*' => 'required|string|max:500', 'education_level' => 'required|string|max:100', 'experience_level' => 'required|string|max:60', 'salary_min' => 'nullable|integer|min:0', 'salary_max' => 'nullable|integer|gte:salary_min', 'currency' => 'required|string|size:3', 'salary_context' => 'nullable|string|max:255', 'demand_level' => 'required|in:low,moderate,high,very_high', 'future_outlook' => 'nullable|string|max:3000', 'icon' => 'nullable|string|max:60', 'is_published' => 'required|boolean', 'skills' => 'required|array|min:1', 'skills.*.id' => 'required|exists:skills,id', 'skills.*.importance' => 'required|in:required,recommended', 'skills.*.weight' => 'required|integer|between:1,10']);
    }

    private function syncSkills(Career $career, array $skills): void
    {
        $career->skills()->sync(collect($skills)->mapWithKeys(fn ($skill) => [$skill['id'] => ['importance' => $skill['importance'], 'weight' => $skill['weight']]])->all());
    }
}
