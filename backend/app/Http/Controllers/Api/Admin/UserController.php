<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $f = $request->validate(['search' => 'nullable|string|max:100', 'role' => 'nullable|in:student,graduate,professional,admin', 'active' => 'nullable|boolean']);
        $items = User::with('profile')->when($f['search'] ?? null, fn ($q, $v) => $q->where(fn ($i) => $i->where('name', 'like', "%{$v}%")->orWhere('email', 'like', "%{$v}%")))->when($f['role'] ?? null, fn ($q, $v) => $q->where('role', $v))->when(array_key_exists('active', $f), fn ($q) => $q->where('is_active', $f['active']))->latest()->paginate(20);

        return response()->json(['success' => true, 'message' => 'Users retrieved.', 'data' => $items]);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'User retrieved.', 'data' => $user->load(['profile', 'skills', 'interests'])->loadCount(['quizAttempts', 'bookmarks'])]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        abort_if($user->id === $request->user()->id && $request->boolean('is_active') === false, 422, 'You cannot deactivate your own administrator account.');
        $data = $request->validate(['is_active' => 'required|boolean']);
        $user->update($data);
        if (! $data['is_active']) {
            $user->tokens()->delete();
        }

        return response()->json(['success' => true, 'message' => $data['is_active'] ? 'User activated.' : 'User deactivated.', 'data' => $user]);
    }
}
