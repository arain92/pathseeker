<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Resource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ResourceController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Admin resources retrieved.', 'data' => Resource::with('tags')->latest()->paginate(20)]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        $tags = $data['tag_ids'];
        unset($data['tag_ids'],$data['file']);
        if ($request->hasFile('file')) {
            $data['file_url'] = Storage::url($request->file('file')->store('resources', 'public'));
        }abort_unless(filled($data['file_url'] ?? null), 422, 'A resource URL or validated file is required.');
        $data['slug'] = str($data['title'])->slug();
        $data['created_by'] = $request->user()->id;
        $item = Resource::create($data);
        $item->tags()->sync($tags);

        return response()->json(['success' => true, 'message' => 'Resource created.', 'data' => $item->load('tags')], 201);
    }

    public function show(Resource $resource): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Admin resource retrieved.', 'data' => $resource->load('tags')]);
    }

    public function update(Request $request, Resource $resource): JsonResponse
    {
        $data = $this->validated($request, $resource);
        $tags = $data['tag_ids'];
        unset($data['tag_ids'],$data['file']);
        if ($request->hasFile('file')) {
            $data['file_url'] = Storage::url($request->file('file')->store('resources', 'public'));
        }$data['slug'] = str($data['title'])->slug();
        $resource->update($data);
        $resource->tags()->sync($tags);

        return response()->json(['success' => true, 'message' => 'Resource updated.', 'data' => $resource->fresh()->load('tags')]);
    }

    public function destroy(Resource $resource): JsonResponse
    {
        abort_if($resource->bookmarks()->exists(), 409, 'Unpublish resources with user bookmarks instead of deleting them.');
        $resource->delete();

        return response()->json(['success' => true, 'message' => 'Resource deleted.', 'data' => null]);
    }

    private function validated(Request $request, ?Resource $item = null): array
    {
        return $request->validate(['title' => ['required', 'string', 'max:180', Rule::unique('resources', 'title')->ignore($item?->id)], 'description' => 'required|string|max:5000', 'type' => 'required|in:pdf,checklist,infographic,guide', 'audience' => 'required|string|max:40', 'category' => 'required|string|max:80', 'file_url' => 'nullable|url|max:500', 'file' => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png,webp', 'thumbnail_url' => 'nullable|url|max:500', 'is_published' => 'required|boolean', 'tag_ids' => 'required|array|min:1', 'tag_ids.*' => 'integer|exists:tags,id']);
    }
}
