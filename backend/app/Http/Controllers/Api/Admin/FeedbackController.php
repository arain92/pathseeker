<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Notifications\PathSeekerNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $f = $request->validate(['status' => 'nullable|in:new,reviewing,resolved,closed', 'type' => 'nullable|in:bug,suggestion,question,other', 'search' => 'nullable|string|max:100']);
        $items = Feedback::with('user:id,name,email')->when($f['status'] ?? null, fn ($q, $v) => $q->where('status', $v))->when($f['type'] ?? null, fn ($q, $v) => $q->where('type', $v))->when($f['search'] ?? null, fn ($q, $v) => $q->where(fn ($i) => $i->where('subject', 'like', "%{$v}%")->orWhere('message', 'like', "%{$v}%")))->latest()->paginate(15);

        return response()->json(['success' => true, 'message' => 'Feedback queue retrieved.', 'data' => $items]);
    }

    public function update(Request $request, Feedback $feedback): JsonResponse
    {
        $data = $request->validate(['status' => 'required|in:new,reviewing,resolved,closed', 'priority' => 'required|in:low,normal,high,urgent', 'admin_response' => 'nullable|string|max:5000']);
        $responded = filled($data['admin_response'] ?? null);
        $feedback->update([...$data, 'responded_by' => $responded ? $request->user()->id : $feedback->responded_by, 'responded_at' => $responded ? now() : $feedback->responded_at]);
        if ($responded) {
            $feedback->user->notify(new PathSeekerNotification(['kind' => 'feedback_response', 'title' => 'Your feedback has a response', 'message' => $data['admin_response'], 'target' => '/app/feedback', 'feedback_id' => $feedback->id]));
        }

        return response()->json(['success' => true, 'message' => 'Feedback updated.', 'data' => $feedback]);
    }
    public function analytics(): JsonResponse
    {
        $total = Feedback::count();
        $responded = Feedback::whereNotNull('responded_at')->count();
        return response()->json(['success' => true, 'message' => 'Feedback analytics retrieved.', 'data' => ['total' => $total, 'response_rate' => $total ? round(($responded / $total) * 100, 1) : 0, 'by_status' => Feedback::query()->selectRaw('status, COUNT(*) as count')->groupBy('status')->pluck('count', 'status'), 'by_type' => Feedback::query()->selectRaw('type, COUNT(*) as count')->groupBy('type')->pluck('count', 'type'), 'by_priority' => Feedback::query()->selectRaw('priority, COUNT(*) as count')->groupBy('priority')->pluck('count', 'priority'), 'sentiment' => ['positive' => Feedback::where('message', 'like', '%thank%')->orWhere('message', 'like', '%helpful%')->count(), 'needs_attention' => Feedback::whereIn('status', ['new', 'reviewing'])->count()]]]);
    }
}
