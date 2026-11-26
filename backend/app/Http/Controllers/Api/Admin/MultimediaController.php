<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Multimedia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MultimediaController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Admin multimedia retrieved.', 'data' => Multimedia::with('tags')->withCount(['ratings', 'bookmarks'])->latest()->paginate(20)]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        $tags = $data['tag_ids'];
        $careers = $data['career_ids'] ?? [];
        unset($data['tag_ids'],$data['career_ids']);
        $data['slug'] = str($data['title'])->slug();
        $data['created_by'] = $request->user()->id;
        $item = Multimedia::create($data);
        $item->tags()->sync($tags);
        $item->careers()->sync($careers);

        return response()->json(['success' => true, 'message' => 'Multimedia created.', 'data' => $item->load(['tags', 'careers'])], 201);
    }

    public function show(Multimedia $multimedia): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Admin multimedia retrieved.', 'data' => $multimedia->load(['tags', 'careers'])]);
    }

    public function update(Request $request, Multimedia $multimedia): JsonResponse
    {
        $data = $this->validated($request, $multimedia);
        $tags = $data['tag_ids'];
        $careers = $data['career_ids'] ?? [];
        unset($data['tag_ids'],$data['career_ids']);
        $data['slug'] = str($data['title'])->slug();
        $multimedia->update($data);
        $multimedia->tags()->sync($tags);
        $multimedia->careers()->sync($careers);

        return response()->json(['success' => true, 'message' => 'Multimedia updated.', 'data' => $multimedia->fresh()->load(['tags', 'careers'])]);
    }

    public function destroy(Multimedia $multimedia): JsonResponse
    {
        abort_if($multimedia->ratings()->exists() || $multimedia->bookmarks()->exists(), 409, 'Unpublish media with user activity instead of deleting it.');
        $multimedia->delete();

        return response()->json(['success' => true, 'message' => 'Multimedia deleted.', 'data' => null]);
    }

    private function validated(Request $request, ?Multimedia $item = null): array
    {
        return $request->validate(['title' => ['required', 'string', 'max:180', Rule::unique('multimedia', 'title')->ignore($item?->id)], 'description' => 'required|string|max:5000', 'type' => 'required|in:video,podcast,explainer', 'source_url' => 'required|url|max:500', 'thumbnail_url' => 'nullable|url|max:500', 'transcript' => 'nullable|string|max:50000', 'category' => 'required|string|max:80', 'duration_seconds' => 'nullable|integer|min:1|max:86400', 'is_published' => 'required|boolean', 'tag_ids' => 'required|array|min:1', 'tag_ids.*' => 'integer|exists:tags,id', 'career_ids' => 'nullable|array', 'career_ids.*' => 'integer|exists:careers,id']);
    }
}
