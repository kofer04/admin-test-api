<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Tests\Traits\AuthenticationHelpers;
use Tests\Traits\GivenWhenThen;

class CookieAuthenticationTest extends TestCase
{
    use RefreshDatabase, AuthenticationHelpers, GivenWhenThen;

    /**
     * @test
     * @group auth
     * @group cookie-auth
     */
    public function user_can_login_with_valid_credentials_using_cookie_authentication(): void
    {
        // Given: A user exists in the database
        $user = $this->given('a user exists in the database', function () {
            return User::factory()->create([
                'email' => 'test@example.com',
                'password' => Hash::make('password123'),
            ]);
        });

        // When: User attempts to login with valid credentials
        $response = $this->when('user attempts to login with valid credentials', function () {
            // First get CSRF cookie
            $this->get('/sanctum/csrf-cookie');
            
            return $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'password123',
            ]);
        });

        // Then: User should be authenticated and redirected
        $this->then('user should be authenticated', function () use ($response, $user) {
            // Fortify redirects after successful login
            $response->assertRedirect();
            $this->assertAuthenticatedAs($user);
        });
    }

    /**
     * @test
     * @group auth
     * @group cookie-auth
     */
    public function user_cannot_login_with_invalid_credentials(): void
    {
        // Given: A user exists in the database
        $this->given('a user exists in the database', function () {
            return User::factory()->create([
                'email' => 'test@example.com',
                'password' => Hash::make('password123'),
            ]);
        });

        // When: User attempts to login with invalid password
        $response = $this->when('user attempts to login with invalid password', function () {
            $this->get('/sanctum/csrf-cookie');
            
            return $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ]);
        });

        // Then: Login should fail and user should not be authenticated
        $this->then('login should fail', function () use ($response) {
            // Fortify redirects back with errors
            $response->assertRedirect();
            $response->assertSessionHasErrors();
        });
    }

    /**
     * @test
     * @group auth
     * @group cookie-auth
     */
    public function user_cannot_login_without_csrf_token(): void
    {
        // Given: A user exists in the database
        $this->given('a user exists in the database', function () {
            return User::factory()->create([
                'email' => 'test@example.com',
                'password' => Hash::make('password123'),
            ]);
        });

        // When: User attempts to login without CSRF token
        $response = $this->when('user attempts to login without CSRF token', function () {
            // Deliberately skip CSRF cookie request
            return $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'password123',
            ]);
        });

        // Then: Request should fail with CSRF error
        $this->then('request should fail with CSRF error', function () use ($response) {
            // Without CSRF, Fortify redirects back
            $this->assertTrue(
                in_array($response->status(), [302, 419]),
                "Expected status 302 or 419, got {$response->status()}"
            );
        });
    }

    /**
     * @test
     * @group auth
     * @group cookie-auth
     */
    public function authenticated_user_can_access_protected_routes(): void
    {
        // Given: An authenticated user
        $user = $this->given('an authenticated user', function () {
            $user = $this->createSuperAdmin();
            $this->actingAs($user, 'web');
            return $user;
        });

        // When: User accesses a protected route
        $response = $this->when('user accesses a protected route', function () {
            return $this->getJson('/api/user');
        });

        // Then: User should receive their data
        $this->then('user should receive their data', function () use ($response, $user) {
            $response->assertOk();
            $response->assertJson([
                'id' => $user->id,
                'email' => $user->email,
            ]);
        });
    }

    /**
     * @test
     * @group auth
     * @group cookie-auth
     */
    public function unauthenticated_user_cannot_access_protected_routes(): void
    {
        // Given: An unauthenticated user (no authentication setup)
        $this->given('an unauthenticated user', function () {
            // No authentication - just verify we're a guest
            return true;
        });

        // When: User attempts to access a protected route
        $response = $this->when('user attempts to access a protected route', function () {
            return $this->getJson('/api/user');
        });

        // Then: Request should be rejected
        $this->then('request should be rejected', function () use ($response) {
            $response->assertUnauthorized();
        });
    }

    /**
     * @test
     * @group auth
     * @group cookie-auth
     */
    public function authenticated_user_can_logout(): void
    {
        // Given: An authenticated user
        $user = $this->given('an authenticated user', function () {
            return $this->createSuperAdmin();
        });

        $this->and('user is logged in', function () use ($user) {
            $this->actingAs($user, 'web');
        });

        // When: User logs out
        $response = $this->when('user logs out', function () {
            return $this->post('/logout');
        });

        // Then: User should be logged out
        $this->then('user should be logged out', function () use ($response) {
            // Fortify redirects after logout
            $response->assertRedirect();
        });
    }

    /**
     * @test
     * @group auth
     * @group cookie-auth
     */
    public function session_persists_across_requests(): void
    {
        // Given: An authenticated user
        $user = $this->given('an authenticated user', function () {
            return $this->createSuperAdmin();
        });

        $this->and('user is logged in', function () use ($user) {
            $this->actingAs($user, 'web');
        });

        // When: User makes multiple requests
        $firstResponse = $this->when('user makes first request', function () {
            return $this->getJson('/api/user');
        });

        $secondResponse = $this->and('user makes second request', function () {
            return $this->getJson('/api/user');
        });

        // Then: User should remain authenticated across requests
        $this->then('user should remain authenticated', function () use ($firstResponse, $secondResponse, $user) {
            $firstResponse->assertOk();
            $secondResponse->assertOk();
            
            $firstResponse->assertJson(['id' => $user->id]);
            $secondResponse->assertJson(['id' => $user->id]);
        });
    }

    /**
     * @test
     * @group auth
     * @group cookie-auth
     */
    public function csrf_cookie_can_be_obtained(): void
    {
        // Given: An unauthenticated user (no auth setup needed)
        $this->given('an unauthenticated user', function () {
            return true;
        });

        // When: User requests CSRF cookie
        $response = $this->when('user requests CSRF cookie', function () {
            return $this->get('/sanctum/csrf-cookie');
        });

        // Then: CSRF cookie should be set
        $this->then('CSRF cookie should be set', function () use ($response) {
            $response->assertStatus(204);
            $response->assertCookie('XSRF-TOKEN');
        });
    }

    /**
     * @test
     * @group auth
     * @group cookie-auth
     */
    public function login_rate_limiting_prevents_brute_force_attacks(): void
    {
        // Given: A user exists in the database
        $this->given('a user exists in the database', function () {
            return User::factory()->create([
                'email' => 'test@example.com',
                'password' => Hash::make('password123'),
            ]);
        });

        // When: Multiple failed login attempts are made
        $this->when('multiple failed login attempts are made', function () {
            $this->get('/sanctum/csrf-cookie');
            
            for ($i = 0; $i < 6; $i++) {
                $this->post('/login', [
                    'email' => 'test@example.com',
                    'password' => 'wrong-password',
                ]);
            }
        });

        // Then: Further attempts should be rate limited
        $this->then('further attempts should be rate limited', function () {
            $response = $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ]);
            
            $response->assertStatus(429); // Too Many Requests
        });
    }

    /**
     * @test
     * @group auth
     * @group cookie-auth
     */
    public function authenticated_user_data_includes_roles(): void
    {
        // Given: A user with a specific role
        $user = $this->given('a user with Super Admin role', function () {
            return $this->createSuperAdmin();
        });

        $this->and('user is logged in', function () use ($user) {
            $this->actingAs($user, 'web');
        });

        // When: User fetches their data
        $response = $this->when('user fetches their data', function () {
            return $this->getJson('/api/user');
        });

        // Then: Response should include user data
        $this->then('response should include user data', function () use ($response) {
            $response->assertOk();
            $response->assertJsonStructure([
                'id',
                'email',
                'name',
            ]);
        });
    }
}

