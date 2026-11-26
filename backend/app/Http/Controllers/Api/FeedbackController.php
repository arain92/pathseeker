<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Feedback history retrieved.', 'data' => $request->user()->feedback()->latest()->paginate(10)]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['type' => 'required|in:bug,suggestion,question,other', 'category' => 'required|string|max:80', 'subject' => 'required|string|max:180', 'message' => 'required|string|min:20|max:5000']);
        $item = $request->user()->feedback()->create([...$data, 'status' => 'new', 'priority' => 'normal']);

        return response()->json(['success' => true, 'message' => 'Feedback submitted. We’ll keep the status updated here.', 'data' => $item], 201);
    }
}
