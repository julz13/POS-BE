<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(array $attrs = []): User
    {
        return User::factory()->create($attrs);
    }

    // ── POST /api/auth/login ───────────────────────────────────────────────────

    public function test_login_succeeds_with_correct_credentials(): void
    {
        $user = $this->makeUser(['email' => 'test@example.com', 'password_hash' => 'password']);

        $response = $this->postJson('/api/auth/login', [
            'email'    => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['success', 'data' => ['token', 'user', 'stores']]);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $this->makeUser(['email' => 'test@example.com', 'password_hash' => 'password']);

        $response = $this->postJson('/api/auth/login', [
            'email'    => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422);
    }

    public function test_login_fails_for_nonexistent_user(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email'    => 'nobody@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(422);
    }

    public function test_login_requires_email(): void
    {
        $response = $this->postJson('/api/auth/login', ['password' => 'password']);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    public function test_login_requires_password(): void
    {
        $response = $this->postJson('/api/auth/login', ['email' => 'test@example.com']);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['password']);
    }

    // ── GET /api/auth/me ───────────────────────────────────────────────────────

    public function test_me_returns_authenticated_user(): void
    {
        $user = $this->makeUser();

        $response = $this->actingAs($user, 'sanctum')
                         ->getJson('/api/auth/me');

        $response->assertStatus(200)
                 ->assertJsonPath('data.email', $user->email);
    }

    public function test_unauthenticated_me_returns_401(): void
    {
        $this->getJson('/api/auth/me')->assertStatus(401);
    }

    // ── POST /api/auth/logout ──────────────────────────────────────────────────

    public function test_logout_returns_success(): void
    {
        $user  = $this->makeUser();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)
             ->postJson('/api/auth/logout')
             ->assertStatus(200)
             ->assertJsonPath('success', true);
    }

    public function test_unauthenticated_logout_returns_401(): void
    {
        $this->postJson('/api/auth/logout')->assertStatus(401);
    }

    // ── POST /api/auth/refresh ─────────────────────────────────────────────────

    public function test_refresh_returns_new_token(): void
    {
        $user  = $this->makeUser();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)
                         ->postJson('/api/auth/refresh');

        $response->assertStatus(200)
                 ->assertJsonStructure(['data' => ['token']]);
    }

    // ── GET /api/health ────────────────────────────────────────────────────────

    public function test_health_check_returns_ok(): void
    {
        $this->getJson('/api/health')
             ->assertStatus(200)
             ->assertJsonPath('status', 'ok');
    }
}
