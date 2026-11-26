<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\SuccessStory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SuccessStoryModerationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $status = $request->validate(['status' => 'nullable|in:pending,approved,rejected'])['status'] ?? 'pending';

        return response()->json(['success' => true, 'message' => 'Story moderation queue retrieved.', 'data' => SuccessStory::with('submitter:id,name,email')->where('status', $status)->latest()->paginate(15)]);
    }

    public function update(Request $request, SuccessStory $story): JsonResponse
    {
        $d = $request->validate(['status' => 'required|in:approved,rejected', 'moderation_notes' => 'nullable|string|max:2000']);
        $story->update([...$d, 'published_at' => $d['status'] === 'approved' ? now() : null]);

        return response()->json(['success' => true, 'message' => 'Story moderation status updated.', 'data' => $story]);
    }
}
