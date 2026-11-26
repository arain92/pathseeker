<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthorizationAndPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admin_can_access_admin_routes(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($student, 'sanctum')->getJson('/api/admin/health')->assertForbidden();
        $this->actingAs($admin, 'sanctum')->getJson('/api/admin/health')->assertOk();
    }

    public function test_password_can_be_reset_and_existing_tokens_are_revoked(): void
    {
        $user = User::factory()->create(['password' => 'CareerPath9']);
        $user->createToken('browser');
        $token = Password::createToken($user);

        $this->postJson('/api/auth/reset-password', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'NewCareer8',
            'password_confirmation' => 'NewCareer8',
        ])->assertOk();

        $this->assertTrue(Hash::check('NewCareer8', $user->fresh()->password));
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_forgot_password_does_not_reveal_if_account_exists(): void
    {
        $this->postJson('/api/auth/forgot-password', ['email' => 'unknown@example.test'])
            ->assertOk()
            ->assertJsonPath('message', 'If an account exists for that email, password reset instructions have been sent.');
    }
}
