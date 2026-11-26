<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\User;
use App\Notifications\PathSeekerNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Announcements retrieved.', 'data' => Announcement::with('creator:id,name')->latest()->paginate(15)]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['title' => 'required|string|max:180', 'message' => 'required|string|max:5000', 'target_role' => 'required|in:everyone,student,graduate,professional', 'publish' => 'required|boolean']);
        $item = Announcement::create([...$data, 'created_by' => $request->user()->id, 'is_published' => $data['publish'], 'published_at' => $data['publish'] ? now() : null]);
        if ($data['publish']) {
            $this->send($item);
        }

        return response()->json(['success' => true, 'message' => $data['publish'] ? 'Announcement published.' : 'Announcement saved as draft.', 'data' => $item], 201);
    }

    private function send(Announcement $item): void
    {
        User::query()->where('role', '!=', 'admin')->when($item->target_role !== 'everyone', fn ($q) => $q->where('role', $item->target_role))->where('is_active', true)->eachById(fn (User $user) => $user->notify(new PathSeekerNotification(['kind' => 'announcement', 'title' => $item->title, 'message' => $item->message, 'target' => '/app/notifications', 'announcement_id' => $item->id])));
    }
}
