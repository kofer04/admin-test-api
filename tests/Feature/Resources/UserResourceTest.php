<?php

namespace Tests\Feature\Resources;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\AuthenticationHelpers;
use Tests\Traits\GivenWhenThen;
use Tests\Traits\MarketHelpers;

class UserResourceTest extends TestCase
{
    use RefreshDatabase, AuthenticationHelpers, GivenWhenThen, MarketHelpers;

    /**
     * @test
     * @group resources
     * @group users
     * @group rbac
     */
    public function super_admin_can_view_all_users(): void
    {
        // Given: A Super Admin user
        $admin = $this->given('a Super Admin user exists', function () {
            return $this->createSuperAdmin();
        });

        $this->and('other users exist in the system', function () {
            User::factory()->count(3)->create();
        });

        $this->and('user is authenticated', function () use ($admin) {
            $this->actingAs($admin, 'web');
        });

        // When: Admin requests the users list
        $response = $this->when('admin requests the users list', function () {
            return $this->getJson('/api/v1/users');
        });

        // Then: All users should be returned
        $this->then('all users should be returned', function () use ($response) {
            $response->assertOk();
            $response->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'email'],
                ],
            ]);
        });
    }

    /**
     * @test
     * @group resources
     * @group users
     * @group rbac
     */
    public function market_user_cannot_view_users_without_permission(): void
    {
        // Given: A Market User without user:read permission
        $marketUser = $this->given('a Market User without user:read permission exists', function () {
            return $this->createMarketUser([
                'email' => 'marketuser@test.com',
            ], []); // No user permissions
        });

        $this->and('user is authenticated', function () use ($marketUser) {
            $this->actingAs($marketUser, 'web');
        });

        // When: Market User requests the users list
        $response = $this->when('market user requests the users list', function () {
            return $this->getJson('/api/v1/users');
        });

        // Then: Request should be forbidden
        $this->then('request should be forbidden', function () use ($response) {
            $response->assertForbidden();
        });
    }

    /**
     * @test
     * @group resources
     * @group users
     * @group rbac
     */
    public function unauthenticated_user_cannot_view_users(): void
    {
        // Given: An unauthenticated user (no auth setup)
        $this->given('an unauthenticated user', function () {
            return true;
        });

        // When: User attempts to access users
        $response = $this->when('user attempts to access users', function () {
            return $this->getJson('/api/v1/users');
        });

        // Then: Request should be unauthorized
        $this->then('request should be unauthorized', function () use ($response) {
            $response->assertUnauthorized();
        });
    }

    /**
     * @test
     * @group resources
     * @group users
     * @group export
     * @group rbac
     */
    public function super_admin_can_export_users_as_csv(): void
    {
        // Given: A Super Admin user
        $admin = $this->given('a Super Admin user exists', function () {
            return $this->createSuperAdmin();
        });

        $this->and('some users exist', function () {
            User::factory()->count(3)->create();
        });

        $this->and('user is authenticated', function () use ($admin) {
            $this->actingAs($admin, 'web');
        });

        // When: Admin requests to export users
        $response = $this->when('admin requests to export users', function () {
            return $this->get('/api/v1/users/export');
        });

        // Then: CSV file should be downloaded
        $this->then('CSV file should be downloaded', function () use ($response) {
            $response->assertOk();
            $this->assertEquals('text/csv; charset=UTF-8', $response->headers->get('Content-Type'));
        });
    }

    /**
     * @test
     * @group resources
     * @group users
     * @group export
     * @group rbac
     */
    public function market_user_without_export_permission_cannot_export_users(): void
    {
        // Given: A Market User without export permission
        $marketUser = $this->given('a Market User without export permission exists', function () {
            return $this->createMarketUser([
                'email' => 'marketuser@test.com',
            ], []); // No permissions
        });

        $this->and('user is authenticated', function () use ($marketUser) {
            $this->actingAs($marketUser, 'web');
        });

        // When: Market User attempts to export users
        $response = $this->when('market user attempts to export users', function () {
            return $this->get('/api/v1/users/export');
        });

        // Then: Request should be forbidden
        $this->then('request should be forbidden', function () use ($response) {
            $response->assertForbidden();
        });
    }

    /**
     * @test
     * @group resources
     * @group users
     * @group self-access
     */
    public function user_can_view_their_own_profile(): void
    {
        // Given: A market user
        $user = $this->given('a market user exists', function () {
            return $this->createMarketUser([
                'email' => 'marketuser@test.com',
            ]);
        });

        $this->and('user is authenticated', function () use ($user) {
            $this->actingAs($user, 'web');
        });

        // When: User requests their own data via the /api/user endpoint
        $response = $this->when('user requests their own profile', function () {
            return $this->getJson('/api/user');
        });

        // Then: User should receive their data
        $this->then('user should receive their own data', function () use ($response, $user) {
            $response->assertOk();
            $response->assertJson([
                'id' => $user->id,
                'email' => $user->email,
            ]);
        });
    }
}
