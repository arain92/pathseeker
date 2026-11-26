<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Multimedia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MultimediaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $f = $request->validate(['search' => 'nullable|string|max:100', 'type' => 'nullable|in:video,podcast,explainer', 'category' => 'nullable|string|max:80']);
        $items = Multimedia::with('tags')->withAvg('ratings', 'rating')->withCount('ratings')->where('is_published', true)->when($f['search'] ?? null, fn ($q, $s) => $q->where(fn ($i) => $i->where('title', 'like', "%{$s}%")->orWhere('description', 'like', "%{$s}%")))->when($f['type'] ?? null, fn ($q, $v) => $q->where('type', $v))->when($f['category'] ?? null, fn ($q, $v) => $q->where('category', $v))->latest()->paginate(12);

        return response()->json(['success' => true, 'message' => 'Multimedia retrieved.', 'data' => $items->items(), 'meta' => ['current_page' => $items->currentPage(), 'last_page' => $items->lastPage(), 'total' => $items->total()]]);
    }

    public function show(string $slug): JsonResponse
    {
        $item = Multimedia::with(['tags', 'careers.domain'])->withAvg('ratings', 'rating')->withCount('ratings')->where('slug', $slug)->where('is_published', true)->firstOrFail();
        $item->increment('view_count');
        $item->setAttribute('related', Multimedia::query()->where('is_published', true)->where('category', $item->category)->whereKeyNot($item->getKey())->latest()->limit(3)->get(['id','title','slug','type','category']));

        return response()->json(['success' => true, 'message' => 'Multimedia retrieved.', 'data' => $item]);
    }

    public function rate(Request $request, Multimedia $multimedia): JsonResponse
    {
        abort_unless($multimedia->is_published, 404);
        $data = $request->validate(['rating' => 'required|integer|between:1,5']);
        $request->user()->mediaRatings()->updateOrCreate(['multimedia_id' => $multimedia->id], $data);
        $stats = $multimedia->ratings()->selectRaw('AVG(rating) average, COUNT(*) count')->first();

        return response()->json(['success' => true, 'message' => 'Rating saved.', 'data' => ['average' => (float) $stats->average, 'count' => (int) $stats->count, 'user_rating' => $data['rating']]]);
    }
}
