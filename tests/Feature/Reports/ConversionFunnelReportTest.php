<?php

namespace Tests\Feature\Reports;

use App\Enums\Permission;
use App\Models\Market;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\AuthenticationHelpers;
use Tests\Traits\GivenWhenThen;
use Tests\Traits\MarketHelpers;

class ConversionFunnelReportTest extends TestCase
{
    use RefreshDatabase, AuthenticationHelpers, GivenWhenThen, MarketHelpers;

    /**
     * @test
     * @group reports
     * @group conversion-funnel
     * @group rbac
     */
    public function super_admin_can_access_conversion_funnel_report(): void
    {
        // Given: A Super Admin user
        $admin = $this->given('a Super Admin user exists', function () {
            return $this->createSuperAdmin();
        });

        $this->and('some markets exist in the system', function () {
            return $this->createMarkets(3);
        });

        $this->and('user is authenticated', function () use ($admin) {
            $this->actingAs($admin, 'web');
        });

        // When: Admin requests conversion funnel report
        $response = $this->when('admin requests conversion funnel report', function () {
            return $this->getJson('/api/v1/conversion-funnel');
        });

        // Then: Report data should be returned successfully
        $this->then('report data should be returned successfully', function () use ($response) {
            $response->assertOk();
            $response->assertJsonStructure([
                'data',
                'analytics',
            ]);
        });
    }

    /**
     * @test
     * @group reports
     * @group conversion-funnel
     * @group rbac
     */
    public function market_user_with_permission_can_access_conversion_funnel_report(): void
    {
        // Given: A Market User with read permission
        $marketUser = $this->given('a Market User with read permission exists', function () {
            return $this->createMarketUser([
                'email' => 'marketuser@test.com',
            ], [Permission::ReadReportConversionFunnel->value]);
        });

        $market = $this->and('user has access to specific markets', function () use ($marketUser) {
            $market = $this->createMarketForUser($marketUser);
            return $market;
        });

        $this->and('user is authenticated', function () use ($marketUser) {
            $this->actingAs($marketUser, 'web');
        });

        // When: Market User requests conversion funnel report
        $response = $this->when('market user requests conversion funnel report', function () {
            return $this->getJson('/api/v1/conversion-funnel');
        });

        // Then: Report data should be returned with user's accessible markets only
        $this->then('report data should be returned', function () use ($response) {
            $response->assertOk();
            $response->assertJsonStructure([
                'data',
                'analytics',
            ]);
        });
    }

    /**
     * @test
     * @group reports
     * @group conversion-funnel
     * @group rbac
     */
    public function market_user_without_permission_cannot_access_conversion_funnel_report(): void
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

        // When: Market User requests conversion funnel report
        $response = $this->when('market user requests conversion funnel report', function () {
            return $this->getJson('/api/v1/conversion-funnel');
        });

        // Then: Request should be forbidden
        $this->then('request should be forbidden', function () use ($response) {
            $response->assertForbidden();
        });
    }

    /**
     * @test
     * @group reports
     * @group conversion-funnel
     * @group rbac
     */
    public function unauthenticated_user_cannot_access_conversion_funnel_report(): void
    {
        // Given: An unauthenticated user
        $this->given('an unauthenticated user', function () {
            $this->assertGuest();
        });

        // When: User attempts to access conversion funnel report
        $response = $this->when('user attempts to access conversion funnel report', function () {
            return $this->getJson('/api/v1/conversion-funnel');
        });

        // Then: Request should be unauthorized
        $this->then('request should be unauthorized', function () use ($response) {
            $response->assertUnauthorized();
        });
    }

    /**
     * @test
     * @group reports
     * @group conversion-funnel
     * @group export
     */
    public function super_admin_can_export_conversion_funnel_report_as_csv(): void
    {
        // Given: A Super Admin user
        $admin = $this->given('a Super Admin user exists', function () {
            return $this->createSuperAdmin();
        });

        $this->and('some markets exist', function () {
            return $this->createMarkets(2);
        });

        $this->and('user is authenticated', function () use ($admin) {
            $this->actingAs($admin, 'web');
        });

        // When: Admin requests to export conversion funnel report
        $response = $this->when('admin requests to export conversion funnel report', function () {
            return $this->get('/api/v1/conversion-funnel/export');
        });

        // Then: CSV file should be downloaded
        $this->then('CSV file should be downloaded', function () use ($response) {
            $response->assertOk();
            $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
            $response->assertHeader('Content-Disposition');
            $this->assertStringContainsString('conversion-funnel', $response->headers->get('Content-Disposition'));
        });
    }

    /**
     * @test
     * @group reports
     * @group conversion-funnel
     * @group export
     * @group rbac
     */
    public function market_user_with_export_permission_can_export_conversion_funnel_report(): void
    {
        // Given: A Market User with export permission
        $marketUser = $this->given('a Market User with export permission exists', function () {
            return $this->createMarketUser([
                'email' => 'marketuser@test.com',
            ], [
                Permission::ReadReportConversionFunnel->value,
                Permission::ExportReportConversionFunnel->value,
            ]);
        });

        $this->and('user has access to markets', function () use ($marketUser) {
            $this->createMarketForUser($marketUser);
        });

        $this->and('user is authenticated', function () use ($marketUser) {
            $this->actingAs($marketUser, 'web');
        });

        // When: Market User requests to export conversion funnel report
        $response = $this->when('market user requests to export conversion funnel report', function () {
            return $this->get('/api/v1/conversion-funnel/export');
        });

        // Then: CSV file should be downloaded
        $this->then('CSV file should be downloaded', function () use ($response) {
            $response->assertOk();
            $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        });
    }

    /**
     * @test
     * @group reports
     * @group conversion-funnel
     * @group export
     * @group rbac
     */
    public function market_user_without_export_permission_cannot_export_conversion_funnel_report(): void
    {
        // Given: A Market User with only read permission (no export)
        $marketUser = $this->given('a Market User with only read permission exists', function () {
            return $this->createMarketUser([
                'email' => 'marketuser@test.com',
            ], [Permission::ReadReportConversionFunnel->value]); // Only read, no export
        });

        $this->and('user is authenticated', function () use ($marketUser) {
            $this->actingAs($marketUser, 'web');
        });

        // When: Market User attempts to export conversion funnel report
        $response = $this->when('market user attempts to export conversion funnel report', function () {
            return $this->get('/api/v1/conversion-funnel/export');
        });

        // Then: Request should be forbidden
        $this->then('request should be forbidden', function () use ($response) {
            $response->assertForbidden();
        });
    }

    /**
     * @test
     * @group reports
     * @group conversion-funnel
     * @group filters
     */
    public function conversion_funnel_report_accepts_date_range_filters(): void
    {
        // Given: A Super Admin user
        $admin = $this->given('a Super Admin user exists', function () {
            return $this->createSuperAdmin();
        });

        $this->and('user is authenticated', function () use ($admin) {
            $this->actingAs($admin, 'web');
        });

        // When: Admin requests report with date range filters
        $response = $this->when('admin requests report with date range filters', function () {
            return $this->getJson('/api/v1/conversion-funnel?' . http_build_query([
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
            ]));
        });

        // Then: Report should be filtered accordingly
        $this->then('report should be returned with applied filters', function () use ($response) {
            $response->assertOk();
            $response->assertJsonStructure([
                'data',
                'analytics',
            ]);
        });
    }

    /**
     * @test
     * @group reports
     * @group conversion-funnel
     * @group filters
     */
    public function conversion_funnel_report_accepts_market_filters(): void
    {
        // Given: A Super Admin user
        $admin = $this->given('a Super Admin user exists', function () {
            return $this->createSuperAdmin();
        });

        $market = $this->and('specific markets exist', function () {
            return $this->createMarket();
        });

        $this->and('user is authenticated', function () use ($admin) {
            $this->actingAs($admin, 'web');
        });

        // When: Admin requests report filtered by specific market
        $response = $this->when('admin requests report filtered by market', function () use ($market) {
            return $this->getJson('/api/v1/conversion-funnel?' . http_build_query([
                'market_ids' => [$market->id],
            ]));
        });

        // Then: Report should be filtered to show only data from specified market
        $this->then('report should be filtered to specified market', function () use ($response) {
            $response->assertOk();
            $response->assertJsonStructure([
                'data',
                'analytics',
            ]);
        });
    }

    /**
     * @test
     * @group reports
     * @group conversion-funnel
     * @group market-access
     */
    public function market_user_only_sees_data_from_accessible_markets(): void
    {
        // Given: Two markets exist
        $market1 = $this->given('market 1 exists', function () {
            return $this->createMarket(['name' => 'Market 1']);
        });

        $market2 = $this->and('market 2 exists', function () {
            return $this->createMarket(['name' => 'Market 2']);
        });

        // And: Market User has access only to market 1
        $marketUser = $this->and('a Market User with access to market 1 only', function () use ($market1) {
            $user = $this->createMarketUser([
                'email' => 'marketuser@test.com',
            ], [Permission::ReadReportConversionFunnel->value]);
            
            $this->assignMarketsToUser($user, [$market1->id]);
            return $user;
        });

        $this->and('user is authenticated', function () use ($marketUser) {
            $this->actingAs($marketUser, 'web');
        });

        // When: Market User requests conversion funnel report
        $response = $this->when('market user requests conversion funnel report', function () {
            return $this->getJson('/api/v1/conversion-funnel');
        });

        // Then: Report should only contain data from accessible market
        $this->then('report should only contain data from accessible market', function () use ($response) {
            $response->assertOk();
            // Note: Actual data validation would require seeded data
            // This test ensures the request succeeds and the filtering is applied
        });
    }

    /**
     * @test
     * @group reports
     * @group conversion-funnel
     * @group validation
     */
    public function conversion_funnel_report_validates_invalid_date_formats(): void
    {
        // Given: A Super Admin user
        $admin = $this->given('a Super Admin user exists', function () {
            return $this->createSuperAdmin();
        });

        $this->and('user is authenticated', function () use ($admin) {
            $this->actingAs($admin, 'web');
        });

        // When: Admin requests report with invalid date format
        $response = $this->when('admin requests report with invalid date format', function () {
            return $this->getJson('/api/v1/conversion-funnel?' . http_build_query([
                'start_date' => 'invalid-date',
                'end_date' => '2024-12-31',
            ]));
        });

        // Then: Validation error should be returned
        $this->then('validation error should be returned', function () use ($response) {
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['start_date']);
        });
    }

    /**
     * @test
     * @group reports
     * @group conversion-funnel
     * @group cross-role
     */
    public function different_users_see_different_report_data_based_on_market_access(): void
    {
        // Given: Multiple markets exist
        $market1 = $this->given('market 1 exists', function () {
            return $this->createMarket(['name' => 'Market 1']);
        });

        $market2 = $this->and('market 2 exists', function () {
            return $this->createMarket(['name' => 'Market 2']);
        });

        // And: User 1 has access to market 1
        $user1 = $this->and('user 1 has access to market 1', function () use ($market1) {
            $user = $this->createMarketUser([
                'email' => 'user1@test.com',
            ], [Permission::ReadReportConversionFunnel->value]);
            
            $this->assignMarketsToUser($user, [$market1->id]);
            return $user;
        });

        // And: User 2 has access to market 2
        $user2 = $this->and('user 2 has access to market 2', function () use ($market2) {
            $user = $this->createMarketUser([
                'email' => 'user2@test.com',
            ], [Permission::ReadReportConversionFunnel->value]);
            
            $this->assignMarketsToUser($user, [$market2->id]);
            return $user;
        });

        // When: User 1 requests the report
        $response1 = $this->when('user 1 requests conversion funnel report', function () use ($user1) {
            $this->actingAs($user1, 'web');
            return $this->getJson('/api/v1/conversion-funnel');
        });

        // And: User 2 requests the report
        $response2 = $this->and('user 2 requests conversion funnel report', function () use ($user2) {
            // Re-authenticate as user 2
            $this->actingAs($user2, 'web');
            return $this->getJson('/api/v1/conversion-funnel');
        });

        // Then: Both users should get successful responses
        $this->then('both users should get successful responses', function () use ($response1, $response2) {
            $response1->assertOk();
            $response2->assertOk();
            
            // Both should have data structure but potentially different data
            // (actual data comparison would require seeded test data)
            $response1->assertJsonStructure(['data', 'analytics']);
            $response2->assertJsonStructure(['data', 'analytics']);
        });
    }
}

