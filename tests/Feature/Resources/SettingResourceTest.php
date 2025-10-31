<?php

namespace Tests\Feature\Resources;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\AuthenticationHelpers;
use Tests\Traits\GivenWhenThen;
use Tests\Traits\MarketHelpers;

class SettingResourceTest extends TestCase
{
    use RefreshDatabase, AuthenticationHelpers, GivenWhenThen, MarketHelpers;

    /**
     * @test
     * @group resources
     * @group settings
     * @group rbac
     */
    public function authenticated_user_can_view_their_settings(): void
    {
        // Given: A market user
        $user = $this->given('a market user exists', function () {
            return $this->createMarketUser(['email' => 'marketuser@test.com']);
        });

        $this->and('user is authenticated', function () use ($user) {
            $this->actingAs($user, 'web');
        });

        // When: User requests their settings
        $response = $this->when('user requests their settings', function () {
            return $this->getJson('/api/v1/user/settings');
        });

        // Then: User should receive their settings
        $this->then('user should receive their settings', function () use ($response) {
            $response->assertOk();
            $response->assertJsonStructure(['data']);
        });
    }

    /**
     * @test
     * @group resources
     * @group settings
     * @group rbac
     */
    public function super_admin_can_view_settings(): void
    {
        // Given: System-wide settings exist
        $this->given('system-wide settings exist', function () {
            Setting::create([
                'key' => 'conversion_funnel_step_1',
                'value' => '2',
                'type' => 'integer',
                'description' => 'Job Type & Zip Completed',
            ]);
        });

        $admin = $this->and('a Super Admin user exists', function () {
            return $this->createSuperAdmin();
        });

        $this->and('admin is authenticated', function () use ($admin) {
            $this->actingAs($admin, 'web');
        });

        // When: Admin requests their settings
        $response = $this->when('admin requests their settings', function () {
            return $this->getJson('/api/v1/user/settings');
        });

        // Then: Admin should receive settings
        $this->then('admin should receive settings', function () use ($response) {
            $response->assertOk();
            $response->assertJsonStructure(['data']);
        });
    }

    /**
     * @test
     * @group resources
     * @group settings
     * @group rbac
     */
    public function unauthenticated_user_cannot_view_settings(): void
    {
        // Given: An unauthenticated user (no auth setup)
        $this->given('an unauthenticated user', function () {
            return true;
        });

        // When: User attempts to access settings
        $response = $this->when('user attempts to access settings', function () {
            return $this->getJson('/api/v1/user/settings');
        });

        // Then: Request should be unauthorized
        $this->then('request should be unauthorized', function () use ($response) {
            $response->assertUnauthorized();
        });
    }

    /**
     * @test
     * @group resources
     * @group settings
     * @group update
     */
    public function user_can_update_their_own_settings(): void
    {
        // Given: A user exists
        $user = $this->given('a user exists', function () {
            return $this->createMarketUser(['email' => 'marketuser@test.com']);
        });

        $this->and('user is authenticated', function () use ($user) {
            $this->actingAs($user, 'web');
        });

        // When: User updates their setting
        $response = $this->when('user updates their setting', function () {
            return $this->putJson('/api/v1/user/settings', [
                'key' => 'theme',
                'value' => 'dark',
            ]);
        });

        // Then: Setting should be updated successfully
        $this->then('setting should be updated successfully', function () use ($response, $user) {
            $this->assertTrue(
                in_array($response->status(), [200, 201]),
                "Expected status 200 or 201, got {$response->status()}"
            ); // OK or Created
            $response->assertJsonStructure([
                'data' => ['id', 'key', 'value', 'type'],
            ]);
            
            // Verify in database
            $this->assertEquals('dark', $user->fresh()->getSetting('theme'));
        });
    }

    /**
     * @test
     * @group resources
     * @group settings
     * @group type-casting
     */
    public function settings_support_different_data_types(): void
    {
        // Given: A user exists
        $user = $this->given('a user exists', function () {
            return $this->createMarketUser(['email' => 'marketuser@test.com']);
        });

        $this->and('user is authenticated', function () use ($user) {
            $this->actingAs($user, 'web');
        });

        // When: User creates string setting
        $stringResponse = $this->when('user creates a string setting', function () {
            return $this->putJson('/api/v1/user/settings', [
                'key' => 'username',
                'value' => 'john_doe',
            ]);
        });

        // And: User creates integer setting
        $integerResponse = $this->and('user creates an integer setting', function () {
            return $this->putJson('/api/v1/user/settings', [
                'key' => 'age',
                'value' => 25,
            ]);
        });

        // And: User creates boolean setting
        $booleanResponse = $this->and('user creates a boolean setting', function () {
            return $this->putJson('/api/v1/user/settings', [
                'key' => 'notifications',
                'value' => true,
            ]);
        });

        // And: User creates array setting
        $arrayResponse = $this->and('user creates an array setting', function () {
            return $this->putJson('/api/v1/user/settings', [
                'key' => 'preferences',
                'value' => ['theme' => 'dark', 'language' => 'en'],
            ]);
        });

        // Then: All settings should be created with correct types
        $this->then('all settings should be created with correct types', function () use (
            $stringResponse, $integerResponse, $booleanResponse, $arrayResponse
        ) {
            $this->assertTrue(in_array($stringResponse->status(), [200, 201]));
            $this->assertTrue(in_array($integerResponse->status(), [200, 201]));
            $this->assertTrue(in_array($booleanResponse->status(), [200, 201]));
            $this->assertTrue(
                in_array($arrayResponse->status(), [200, 201]),
                "Array response status: {$arrayResponse->status()}"
            );
            
            // Verify types
            $stringResponse->assertJson(['data' => ['type' => 'string']]);
            $integerResponse->assertJson(['data' => ['type' => 'integer']]);
            $booleanResponse->assertJson(['data' => ['type' => 'boolean']]);
            $arrayResponse->assertJson(['data' => ['type' => 'json']]);
        });
    }

    /**
     * @test
     * @group resources
     * @group settings
     * @group validation
     */
    public function setting_update_requires_valid_key(): void
    {
        // Given: A user exists
        $user = $this->given('a user exists', function () {
            return $this->createMarketUser(['email' => 'marketuser@test.com']);
        });

        $this->and('user is authenticated', function () use ($user) {
            $this->actingAs($user, 'web');
        });

        // When: User attempts to update setting without key
        $response = $this->when('user attempts to update setting without key', function () {
            return $this->putJson('/api/v1/user/settings', [
                'value' => 'some value',
            ]);
        });

        // Then: Validation error should be returned
        $this->then('validation error should be returned', function () use ($response) {
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['key']);
        });
    }
}
