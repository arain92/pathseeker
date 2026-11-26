<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_student_can_register_and_receives_a_token(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Areeba Khan',
            'email' => 'areeba@example.test',
            'password' => 'CareerPath9',
            'password_confirmation' => 'CareerPath9',
            'role' => 'student',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.role', 'student')
            ->assertJsonStructure(['data' => ['token']]);

        $token = $response->json('data.token');
        $this->withToken($token)->getJson('/api/profile')->assertOk();

        $this->assertDatabaseHas('users', ['email' => 'areeba@example.test', 'role' => 'student']);
        $this->assertDatabaseCount('user_profiles', 1);
    }

    public function test_admin_cannot_be_registered_publicly(): void
    {
        $this->postJson('/api/auth/register', [
            'name' => 'Unauthorized Admin',
            'email' => 'admin-attempt@example.test',
            'password' => 'CareerPath9',
            'password_confirmation' => 'CareerPath9',
            'role' => 'admin',
        ])->assertUnprocessable()->assertJsonValidationErrors('role');
    }

    public function test_an_active_user_can_login_view_identity_and_logout(): void
    {
        $user = User::factory()->create(['password' => 'CareerPath9', 'role' => 'graduate']);

        $token = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'CareerPath9',
        ])->assertOk()->json('data.token');

        $this->withToken($token)->getJson('/api/auth/me')
            ->assertOk()->assertJsonPath('data.role', 'graduate');

        $this->withToken($token)->postJson('/api/auth/logout')->assertOk();
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/auth/me')->assertUnauthorized();
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create(['password' => 'CareerPath9', 'is_active' => false]);

        $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'CareerPath9'])
            ->assertForbidden();
    }
}
