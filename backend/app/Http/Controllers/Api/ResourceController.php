<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Resource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResourceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $f = $request->validate(['search' => 'nullable|string|max:100', 'type' => 'nullable|in:pdf,checklist,infographic,guide', 'category' => 'nullable|string|max:80', 'audience' => 'nullable|string|max:40', 'sort' => 'nullable|in:newest,popular,title', 'page' => 'nullable|integer|min:1']);
        $q = Resource::with('tags')->where('is_published', true)->when($f['search'] ?? null, fn ($q, $s) => $q->where(fn ($i) => $i->where('title', 'like', "%{$s}%")->orWhere('description', 'like', "%{$s}%")->orWhereHas('tags', fn ($t) => $t->where('name', 'like', "%{$s}%"))))->when($f['type'] ?? null, fn ($q, $v) => $q->where('type', $v))->when($f['category'] ?? null, fn ($q, $v) => $q->where('category', $v))->when($f['audience'] ?? null, fn ($q, $v) => $q->where('audience', $v));
        match ($f['sort'] ?? 'newest') {
            'popular' => $q->orderByDesc('download_count'),'title' => $q->orderBy('title'),default => $q->latest()
        };
        $items = $q->paginate(12);

        return response()->json(['success' => true, 'message' => 'Resources retrieved.', 'data' => $items->items(), 'meta' => ['current_page' => $items->currentPage(), 'last_page' => $items->lastPage(), 'total' => $items->total()]]);
    }

    public function show(string $slug): JsonResponse
    {
        $item = Resource::with('tags')->where('slug', $slug)->where('is_published', true)->firstOrFail();

        return response()->json(['success' => true, 'message' => 'Resource retrieved.', 'data' => $item]);
    }

    public function download(string $slug): JsonResponse
    {
        $item = Resource::where('slug', $slug)->where('is_published', true)->firstOrFail();
        $item->increment('download_count');

        return response()->json(['success' => true, 'message' => 'Download recorded.', 'data' => ['url' => $item->file_url, 'download_count' => $item->download_count]]);
    }
}
