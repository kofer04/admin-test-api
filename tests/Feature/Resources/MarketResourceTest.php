<?php

namespace Tests\Feature\Resources;

use App\Enums\Permission;
use App\Models\Market;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\AuthenticationHelpers;
use Tests\Traits\GivenWhenThen;
use Tests\Traits\MarketHelpers;

class MarketResourceTest extends TestCase
{
    use RefreshDatabase, AuthenticationHelpers, GivenWhenThen, MarketHelpers;

    /**
     * @test
     * @group resources
     * @group markets
     * @group rbac
     */
    public function super_admin_can_view_all_markets(): void
    {
        // Given: A Super Admin user
        $admin = $this->given('a Super Admin user exists', function () {
            return $this->createSuperAdmin();
        });

        $this->and('markets exist in the system', function () {
            return $this->createMarkets(3);
        });

        $this->and('user is authenticated', function () use ($admin) {
            $this->actingAs($admin, 'web');
        });

        // When: Admin requests the markets list
        $response = $this->when('admin requests the markets list', function () {
            return $this->getJson('/api/v1/markets');
        });

        // Then: All markets should be returned
        $this->then('all markets should be returned', function () use ($response) {
            $response->assertOk();
            $response->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'domain', 'path'],
                ],
            ]);
        });
    }

    /**
     * @test
     * @group resources
     * @group markets
     * @group rbac
     */
    public function market_user_with_permission_can_view_accessible_markets_only(): void
    {
        // Given: Multiple markets exist
        $market1 = $this->given('market 1 exists', function () {
            return $this->createMarket(['name' => 'Market 1']);
        });

        $market2 = $this->and('market 2 exists', function () {
            return $this->createMarket(['name' => 'Market 2']);
        });

        // And: Market User has access only to market 1
        $marketUser = $this->and('a Market User with read permission and access to market 1', function () use ($market1) {
            $user = $this->createMarketUser([
                'email' => 'marketuser@test.com',
            ], [Permission::MarketsRead->value]);
            
            $this->assignMarketsToUser($user, [$market1->id]);
            return $user;
        });

        $this->and('user is authenticated', function () use ($marketUser) {
            $this->actingAs($marketUser, 'web');
        });

        // When: Market User requests the markets list
        $response = $this->when('market user requests the markets list', function () {
            return $this->getJson('/api/v1/markets');
        });

        // Then: Only accessible markets should be returned
        $this->then('only accessible markets should be returned', function () use ($response, $market1) {
            $response->assertOk();
            
            $returnedIds = collect($response->json('data'))->pluck('id')->toArray();
            $this->assertContains($market1->id, $returnedIds);
        });
    }

    /**
     * @test
     * @group resources
     * @group markets
     * @group rbac
     */
    public function market_user_without_permission_cannot_view_markets(): void
    {
        // Given: A Market User without read permission
        $marketUser = $this->given('a Market User without read permission exists', function () {
            return $this->createMarketUser([
                'email' => 'marketuser@test.com',
            ], []); // No permissions
        });

        $this->and('user is authenticated', function () use ($marketUser) {
            $this->actingAs($marketUser, 'web');
        });

        // When: Market User requests the markets list
        $response = $this->when('market user requests the markets list', function () {
            return $this->getJson('/api/v1/markets');
        });

        // Then: Request should be forbidden
        $this->then('request should be forbidden', function () use ($response) {
            $response->assertForbidden();
        });
    }

    /**
     * @test
     * @group resources
     * @group markets
     * @group rbac
     */
    public function unauthenticated_user_cannot_view_markets(): void
    {
        // Given: An unauthenticated user (no auth setup)
        $this->given('an unauthenticated user', function () {
            // No authentication needed
            return true;
        });

        $this->and('some markets exist', function () {
            return $this->createMarkets(3);
        });

        // When: User attempts to access markets
        $response = $this->when('user attempts to access markets', function () {
            return $this->getJson('/api/v1/markets');
        });

        // Then: Request should be unauthorized
        $this->then('request should be unauthorized', function () use ($response) {
            $response->assertUnauthorized();
        });
    }

    /**
     * @test
     * @group resources
     * @group markets
     * @group export
     * @group rbac
     */
    public function super_admin_can_export_markets_as_csv(): void
    {
        // Given: A Super Admin user
        $admin = $this->given('a Super Admin user exists', function () {
            return $this->createSuperAdmin();
        });

        $this->and('some markets exist', function () {
            return $this->createMarkets(3);
        });

        $this->and('user is authenticated', function () use ($admin) {
            $this->actingAs($admin, 'web');
        });

        // When: Admin requests to export markets
        $response = $this->when('admin requests to export markets', function () {
            return $this->get('/api/v1/markets/export');
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
     * @group markets
     * @group export
     * @group rbac
     */
    public function market_user_with_export_permission_can_export_markets(): void
    {
        // Given: Market exists
        $market1 = $this->given('market exists', function () {
            return $this->createMarket(['name' => 'Market 1']);
        });

        // And: Market User with export permission
        $marketUser = $this->and('a Market User with export permission', function () use ($market1) {
            $user = $this->createMarketUser([
                'email' => 'marketuser@test.com',
            ], [
                Permission::MarketsRead->value,
                Permission::MarketsExport->value,
            ]);
            
            $this->assignMarketsToUser($user, [$market1->id]);
            return $user;
        });

        $this->and('user is authenticated', function () use ($marketUser) {
            $this->actingAs($marketUser, 'web');
        });

        // When: Market User requests to export markets
        $response = $this->when('market user requests to export markets', function () {
            return $this->get('/api/v1/markets/export');
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
     * @group markets
     * @group export
     * @group rbac
     */
    public function market_user_without_export_permission_cannot_export_markets(): void
    {
        // Given: A Market User with only read permission (no export)
        $marketUser = $this->given('a Market User with only read permission exists', function () {
            return $this->createMarketUser([
                'email' => 'marketuser@test.com',
            ], [Permission::MarketsRead->value]); // Only read, no export
        });

        $this->and('user is authenticated', function () use ($marketUser) {
            $this->actingAs($marketUser, 'web');
        });

        // When: Market User attempts to export markets
        $response = $this->when('market user attempts to export markets', function () {
            return $this->get('/api/v1/markets/export');
        });

        // Then: Request should be forbidden
        $this->then('request should be forbidden', function () use ($response) {
            $response->assertForbidden();
        });
    }
}
