<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CareerResource;
use App\Models\Career;
use App\Models\RecentView;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->load(['profile', 'skills', 'interests']);
        $latestAttempt = $user->quizAttempts()->where('status', 'completed')->with(['result', 'recommendations' => fn ($q) => $q->with(['career.domain', 'career.skills'])->limit(5)])->latest('completed_at')->first();
        $saved = $user->bookmarks()->where('bookmarkable_type', Career::class)->with('bookmarkable.domain')->latest('updated_at')->limit(4)->get()->filter(fn ($item) => $item->bookmarkable instanceof Career)->values();
        $savedDomainIds = $saved->pluck('bookmarkable.domain_id')->filter()->unique()->values();
        $similar = $savedDomainIds->isNotEmpty() ? Career::query()->with('domain')->where('is_published', true)->whereIn('career_domain_id', $savedDomainIds)->whereNotIn('id', $saved->pluck('bookmarkable.id'))->inRandomOrder()->limit(4)->get() : collect();
        $recent = $user->recentViews()->with('viewable.domain')->latest('last_viewed_at')->limit(4)->get()->filter(fn ($item) => $item->viewable instanceof Career)->values();
        $trendingIds = RecentView::query()->where('viewable_type', Career::class)->selectRaw('viewable_id, SUM(view_count) as engagement_count')->groupBy('viewable_id')->orderByDesc('engagement_count')->limit(5)->pluck('engagement_count', 'viewable_id');
        $trending = Career::query()->with(['domain', 'skills'])->where('is_published', true)->whereKey($trendingIds->keys())->get()->sortBy(fn ($career) => $trendingIds[$career->id])->reverse()->values();

        return response()->json(['success' => true, 'message' => 'Personalized overview retrieved.', 'data' => [
            'passport' => ['completion' => $this->completion($user), 'skills_count' => $user->skills->count(), 'interests_count' => $user->interests->count()],
            'latest_quiz' => $latestAttempt ? ['attempt_id' => $latestAttempt->id, 'completed_at' => $latestAttempt->completed_at, 'dimensions' => $latestAttempt->result?->dimension_scores, 'recommendations' => $latestAttempt->recommendations->map(fn ($recommendation) => ['score' => $recommendation->overall_score, 'factors' => $recommendation->factors, 'career' => new CareerResource($recommendation->career)])] : null,
            'saved' => $saved->map(fn ($bookmark) => ['bookmark_id' => $bookmark->id, 'note' => $bookmark->note, 'career' => new CareerResource($bookmark->bookmarkable)]),
            'recent' => $recent->map(fn ($view) => ['last_viewed_at' => $view->last_viewed_at, 'view_count' => $view->view_count, 'career' => new CareerResource($view->viewable)]),
            'trending' => $trending->map(fn ($career) => ['engagement_count' => (int) $trendingIds[$career->id], 'career' => new CareerResource($career)]),
            'similar' => $similar->map(fn ($career) => ['career' => new CareerResource($career)]),
        ]]);
    }

    private function completion($user): int
    {
        $profile = $user->profile;
        $checks = [filled($profile?->country), filled($profile?->career_goal), filled($profile?->education_level) || filled($profile?->degree), $user->skills->count() >= 3, $user->interests->count() >= 2, match ($user->role) {
            'student' => filled($profile?->current_field), 'graduate' => filled($profile?->field_of_study), 'professional' => filled($profile?->job_title), default => true
        }];

        return (int) round(collect($checks)->filter()->count() / count($checks) * 100);
    }
}
