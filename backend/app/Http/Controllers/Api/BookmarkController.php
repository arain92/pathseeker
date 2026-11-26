<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CareerResource;
use App\Models\Bookmark;
use App\Models\Career;
use App\Services\PersonalizableContent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    public function __construct(private PersonalizableContent $content) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate(['type' => 'nullable|in:career,resource,multimedia,story', 'search' => 'nullable|string|max:100']);
        $items = $request->user()->bookmarks()->with('bookmarkable')
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->where('bookmarkable_type', $this->content->classFor($type)))
            ->latest('updated_at')->get()->filter(fn (Bookmark $item) => $item->bookmarkable)
            ->filter(fn (Bookmark $item) => ! isset($filters['search']) || str_contains(strtolower($this->title($item->bookmarkable)), strtolower($filters['search'])))
            ->values()->map(fn (Bookmark $item) => $this->resource($item));

        return response()->json(['success' => true, 'message' => 'Saved items retrieved.', 'data' => $items]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['type' => 'required|in:career,resource,multimedia,story', 'id' => 'required|integer|min:1', 'note' => 'nullable|string|max:5000']);
        $target = $this->content->resolve($data['type'], $data['id']);
        $bookmark = $request->user()->bookmarks()->firstOrCreate(
            ['bookmarkable_type' => $target::class, 'bookmarkable_id' => $target->getKey()],
            ['note' => $data['note'] ?? null]
        );
        $bookmark->load('bookmarkable');

        return response()->json(['success' => true, 'message' => $bookmark->wasRecentlyCreated ? 'Item saved.' : 'Item was already saved.', 'data' => $this->resource($bookmark)], $bookmark->wasRecentlyCreated ? 201 : 200);
    }

    public function update(Request $request, Bookmark $bookmark): JsonResponse
    {
        abort_unless($bookmark->user_id === $request->user()->id, 404);
        $bookmark->update($request->validate(['note' => 'nullable|string|max:5000']));

        return response()->json(['success' => true, 'message' => 'Private note updated.', 'data' => $this->resource($bookmark->load('bookmarkable'))]);
    }

    public function destroy(Request $request, Bookmark $bookmark): JsonResponse
    {
        abort_unless($bookmark->user_id === $request->user()->id, 404);
        $bookmark->delete();

        return response()->json(['success' => true, 'message' => 'Saved item removed.', 'data' => null]);
    }

    private function resource(Bookmark $item): array
    {
        return ['id' => $item->id, 'type' => $this->content->typeFor($item->bookmarkable), 'note' => $item->note, 'updated_at' => $item->updated_at, 'item' => $item->bookmarkable instanceof Career ? new CareerResource($item->bookmarkable->loadMissing(['domain', 'skills'])) : $item->bookmarkable];
    }

    private function title($item): string
    {
        return $item->title ?? $item->person_name ?? '';
    }
}
