<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create($request->safe()->only(['name', 'email', 'password', 'role']));
        $user->profile()->create();
        $token = $user->createToken($request->string('device_name', 'pathseeker-web')->toString())->plainTextToken;

        return response()->json(['success' => true, 'message' => 'Your Career Passport has been created.', 'data' => ['user' => new UserResource($user->load('profile')), 'token' => $token]], 201);
    }


    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->string('email')->lower()->toString())->first();
        if (! $user || ! Hash::check($request->string('password')->toString(), $user->password)) {
            throw ValidationException::withMessages(['email' => ['The provided credentials are incorrect.']]);
        }
        if (! $user->is_active) {
            return response()->json(['success' => false, 'message' => 'This account is inactive.', 'data' => null], 403);
        }
        $user->forceFill(['last_active_at' => now()])->save();
        $token = $user->createToken($request->string('device_name', 'pathseeker-web')->toString())->plainTextToken;

        return response()->json(['success' => true, 'message' => 'Welcome back.', 'data' => ['user' => new UserResource($user->load('profile')), 'token' => $token]]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Authenticated user retrieved.', 'data' => new UserResource($request->user()->load('profile'))]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['success' => true, 'message' => 'You have been signed out.', 'data' => null]);
    }
}
