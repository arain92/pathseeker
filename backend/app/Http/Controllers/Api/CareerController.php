<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CareerResource;
use App\Models\Career;
use App\Models\CareerDomain;
use App\Models\Skill;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CareerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate(['search' => 'nullable|string|max:100', 'domain' => 'nullable|string|max:120', 'skill' => 'nullable|integer|exists:skills,id', 'education_level' => 'nullable|string|max:100', 'experience_level' => 'nullable|string|max:60', 'demand' => 'nullable|in:low,moderate,high,very_high', 'salary_min' => 'nullable|integer|min:0', 'salary_max' => 'nullable|integer|min:0', 'sort' => 'nullable|in:title,-title,salary,-salary,demand', 'page' => 'nullable|integer|min:1', 'per_page' => 'nullable|integer|between:1,24']);
        $query = Career::query()->with(['domain', 'skills'])->where('is_published', true)
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where(fn ($inner) => $inner->where('title', 'like', "%{$search}%")->orWhere('summary', 'like', "%{$search}%")->orWhereHas('skills', fn ($skills) => $skills->where('name', 'like', "%{$search}%"))))
            ->when($filters['domain'] ?? null, fn ($q, $domain) => $q->whereHas('domain', fn ($d) => $d->where('slug', $domain)))
            ->when($filters['skill'] ?? null, fn ($q, $skill) => $q->whereHas('skills', fn ($s) => $s->whereKey($skill)))
            ->when($filters['education_level'] ?? null, fn ($q, $level) => $q->where('education_level', $level))
            ->when($filters['experience_level'] ?? null, fn ($q, $level) => $q->where('experience_level', $level))
            ->when($filters['demand'] ?? null, fn ($q, $demand) => $q->where('demand_level', $demand))
            ->when($filters['salary_min'] ?? null, fn ($q, $min) => $q->where('salary_max', '>=', $min))
            ->when($filters['salary_max'] ?? null, fn ($q, $max) => $q->where('salary_min', '<=', $max));
        if (!empty($filters['search']) && !(clone $query)->exists()) {
            $needle = strtolower($filters['search']);
            $candidate = Career::query()->where('is_published', true)->get(['id', 'title'])->sortBy(fn ($career) => levenshtein($needle, strtolower($career->title)))->first();
            if ($candidate && levenshtein($needle, strtolower($candidate->title)) <= max(3, (int) floor(strlen($needle) / 3))) {
                $query = Career::query()->with(['domain', 'skills'])->where('is_published', true)->whereKey($candidate->id);
            }
        }
        match ($filters['sort'] ?? 'title') {
            '-title' => $query->orderByDesc('title'),'salary' => $query->orderBy('salary_min'),'-salary' => $query->orderByDesc('salary_max'),'demand' => $query->orderByRaw("FIELD(demand_level, 'very_high','high','moderate','low')"),default => $query->orderBy('title')
        };
        $careers = $query->paginate($filters['per_page'] ?? 12)->withQueryString();

        return response()->json(['success' => true, 'message' => 'Careers retrieved.', 'data' => CareerResource::collection($careers->items()), 'meta' => ['current_page' => $careers->currentPage(), 'last_page' => $careers->lastPage(), 'per_page' => $careers->perPage(), 'total' => $careers->total()]]);
    }

    public function show(string $slug): JsonResponse
    {
        $career = Career::query()->with(['domain', 'skills'])->where('slug', $slug)->where('is_published', true)->firstOrFail();

        return response()->json(['success' => true, 'message' => 'Career retrieved.', 'data' => new CareerResource($career)]);
    }

    public function options(): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Career filter options retrieved.', 'data' => [
            'skills' => Skill::query()->orderBy('name')->get(['id', 'name']),
            'education_levels' => Career::query()->where('is_published', true)->distinct()->orderBy('education_level')->pluck('education_level'),
            'experience_levels' => Career::query()->where('is_published', true)->distinct()->orderBy('experience_level')->pluck('experience_level'),
        ]]);
    }

    public function domains(): JsonResponse
    {
        $domains = CareerDomain::query()->withCount(['careers' => fn ($q) => $q->where('is_published', true)])->orderBy('sort_order')->get();

        return response()->json(['success' => true, 'message' => 'Career domains retrieved.', 'data' => $domains]);
    }
}
