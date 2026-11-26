<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\ProfileResource;
use App\Models\Interest;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->load(['profile', 'skills', 'interests']);

        return response()->json(['success' => true, 'message' => 'Career Passport retrieved.', 'data' => $this->resource($user)]);
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        DB::transaction(function () use ($request, $user): void {
            $userFields = ['name' => $request->string('name')->toString()];
            if ($user->role !== 'admin' && $request->has('role')) {
                $userFields['role'] = $request->validated('role');
            }
            $user->update($userFields);
            $profileFields = collect($request->validated())->except(['name', 'role', 'skills', 'interest_ids'])->all();
            $user->profile()->updateOrCreate([], $profileFields);
            $skills = collect($request->validated('skills', []))->mapWithKeys(fn (array $skill) => [$skill['id'] => ['proficiency' => $skill['proficiency']]])->all();
            $user->skills()->sync($skills);
            $user->interests()->sync($request->validated('interest_ids', []));
        });

        return response()->json(['success' => true, 'message' => 'Career Passport updated.', 'data' => $this->resource($user->fresh()->load(['profile', 'skills', 'interests']))]);
    }

    public function options(): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Profile options retrieved.', 'data' => [
            'skills' => Skill::query()->orderBy('name')->get(['id', 'name']),
            'interests' => Interest::query()->orderBy('name')->get(['id', 'name']),
        ]]);
    }
    public function uploadResume(Request $request): JsonResponse
    {
        $data = $request->validate(['resume' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120']]);
        $path = $data['resume']->store('resumes', 'public');
        $url = Storage::disk('public')->url($path);
        $request->user()->profile()->updateOrCreate([], ['resume_path' => $url]);
        return response()->json(['success' => true, 'message' => 'Resume uploaded.', 'data' => ['resume_path' => $url]]);
    }

    private function resource(User $user): ProfileResource
    {
        $profile = $user->profile;
        $checks = [
            'country' => filled($profile?->country),
            'career_goal' => filled($profile?->career_goal),
            'education' => filled($profile?->education_level) || filled($profile?->degree),
            'skills' => $user->skills->count() >= 3,
            'interests' => $user->interests->count() >= 2,
            'role_details' => match ($user->role) {
                'student' => filled($profile?->current_field),
                'graduate' => filled($profile?->field_of_study),
                'professional' => filled($profile?->job_title),
                default => true,
            },
        ];
        $missing = collect($checks)->filter(fn (bool $complete) => ! $complete)->keys()->values();

        return (new ProfileResource($user))->additional(['completion' => [
            'percentage' => (int) round(collect($checks)->filter()->count() / count($checks) * 100),
            'missing' => $missing,
        ]]);
    }
}
