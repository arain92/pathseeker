<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SuccessStory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SuccessStoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $f = $request->validate(['search' => 'nullable|string|max:100', 'domain' => 'nullable|string|max:100']);
        $items = SuccessStory::where('status', 'approved')->when($f['search'] ?? null, fn ($q, $s) => $q->where(fn ($i) => $i->where('person_name', 'like', "%{$s}%")->orWhere('career_title', 'like', "%{$s}%")->orWhere('summary', 'like', "%{$s}%")))->when($f['domain'] ?? null, fn ($q, $v) => $q->where('domain', $v))->latest('published_at')->paginate(9);

        return response()->json(['success' => true, 'message' => 'Success stories retrieved.', 'data' => $items->items(), 'meta' => ['current_page' => $items->currentPage(), 'last_page' => $items->lastPage(), 'total' => $items->total()]]);
    }

    public function show(string $slug): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Success story retrieved.', 'data' => SuccessStory::where('slug', $slug)->where('status', 'approved')->firstOrFail()]);
    }

    public function store(Request $request): JsonResponse
    {
        $d = $request->validate(['person_name' => 'required|string|max:120', 'career_title' => 'required|string|max:160', 'domain' => 'required|string|max:100', 'summary' => 'required|string|max:2000', 'timeline' => 'required|array|size:5', 'timeline.*' => 'required|string|max:1000', 'advice' => 'required|string|max:3000']);
        $story = $request->user()->successStories()->create([...$d, 'slug' => str($d['person_name'].'-'.$d['career_title'].'-'.str()->random(6))->slug(), 'status' => 'pending']);

        return response()->json(['success' => true, 'message' => 'Your story was submitted for review.', 'data' => $story], 201);
    }

    public function mine(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Your submissions retrieved.', 'data' => $request->user()->successStories()->latest()->get()]);
    }
}
