<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CareerResource;
use App\Models\Career;
use App\Models\RecentView;
use App\Services\PersonalizableContent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecentViewController extends Controller
{
    public function __construct(private PersonalizableContent $content) {}

    public function index(Request $request): JsonResponse
    {
        $items = $request->user()->recentViews()->with('viewable')->latest('last_viewed_at')->limit(20)->get()
            ->filter(fn (RecentView $view) => $view->viewable)->values()->map(fn (RecentView $view) => $this->resource($view));

        return response()->json(['success' => true, 'message' => 'Recent exploration retrieved.', 'data' => $items]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['type' => 'required|in:career', 'id' => 'required|integer|min:1']);
        $target = $this->content->resolve($data['type'], $data['id']);
        $view = $request->user()->recentViews()->where(['viewable_type' => $target::class, 'viewable_id' => $target->getKey()])->first();
        if ($view) {
            $view->update(['last_viewed_at' => now(), 'view_count' => $view->view_count + 1]);
        } else {
            $view = $request->user()->recentViews()->create(['viewable_type' => $target::class, 'viewable_id' => $target->getKey(), 'first_viewed_at' => now(), 'last_viewed_at' => now(), 'view_count' => 1]);
        }
        $view->load('viewable');

        return response()->json(['success' => true, 'message' => 'Exploration recorded.', 'data' => $this->resource($view)], $view->wasRecentlyCreated ? 201 : 200);
    }

    private function resource(RecentView $view): array
    {
        return ['id' => $view->id, 'type' => $this->content->typeFor($view->viewable), 'first_viewed_at' => $view->first_viewed_at, 'last_viewed_at' => $view->last_viewed_at, 'view_count' => $view->view_count, 'item' => $view->viewable instanceof Career ? new CareerResource($view->viewable->loadMissing(['domain', 'skills'])) : null];
    }
}
