<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Career;
use App\Models\Feedback;
use App\Models\Multimedia;
use App\Models\QuizAttempt;
use App\Models\Resource;
use App\Models\SuccessStory;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function show(): JsonResponse
    {
        $roles = User::selectRaw('role, COUNT(*) total')->groupBy('role')->pluck('total', 'role');
        $registrations = User::where('created_at', '>=', now()->subDays(29))->selectRaw('DATE(created_at) day, COUNT(*) total')->groupBy('day')->orderBy('day')->get();
        $topCareers = Career::with('domain:id,name')->withSum('recentViews as engagement', 'view_count')->orderByDesc('engagement')->limit(6)->get(['id', 'career_domain_id', 'title', 'slug']);

        return response()->json(['success' => true, 'message' => 'Administrator dashboard retrieved.', 'data' => ['kpis' => ['users' => User::count(), 'active_users' => User::where('is_active', true)->count(), 'careers' => Career::count(), 'quiz_attempts' => QuizAttempt::where('status', 'completed')->count(), 'pending_stories' => SuccessStory::where('status', 'pending')->count(), 'unresolved_feedback' => Feedback::whereNotIn('status', ['resolved', 'closed'])->count(), 'resource_downloads' => Resource::sum('download_count'), 'multimedia_views' => Multimedia::sum('view_count')], 'users_by_role' => $roles, 'registrations' => $registrations, 'top_careers' => $topCareers, 'feedback_by_type' => Feedback::selectRaw('type, COUNT(*) total')->groupBy('type')->pluck('total', 'type')]]);
    }

    public function options(): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Administrator catalogue options retrieved.', 'data' => [
            'tags' => Tag::orderBy('name')->get(['id', 'name']),
            'careers' => Career::orderBy('title')->get(['id', 'title']),
        ]]);
    }
}
