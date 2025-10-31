<?php

namespace App\Http\Controllers\Api\V1\Actions;

use App\Http\Controllers\Controller;
use App\Http\Resources\MarketResource;
use App\Http\Resources\SettingResource;
use App\Repositories\SettingRepository;
use Illuminate\Http\Request;

/**
 * Single action controller for Sanctum authentication endpoint.
 * Returns raw user data without resource wrapper for nuxt-auth-sanctum compatibility.
 */
class AuthenticatedUserController extends Controller
{
    public function __construct(
        protected SettingRepository $settingRepository
    ) {}

    /**
     * Get the authenticated user with their markets and settings.
     *
     * Returns raw user object (not wrapped in 'data' key) for nuxt-auth-sanctum compatibility.
     *
     * Markets:
     * - Market Users: Returns their assigned markets
     * - Super Admin: Returns empty collection (admins have access to all markets via accessibleMarketIds())
     *
     * Settings:
     * - Market Users: Returns only user-specific settings
     * - Super Admin: Returns merged system-wide + user-specific settings
     */
    public function __invoke(Request $request)
    {
        $user = $request->user();

        // Load markets relationship
        // For admins, this will be empty but they have access to all markets via accessibleMarketIds()
        // For market users, this loads their assigned markets
        $user->load('markets');

        // Get appropriate settings based on user role
        if ($user->isAdmin()) {
            // Super Admin: Get all settings (system-wide + user-specific merged)
            $settings = $this->settingRepository->getAllForUser($user);
        } else {
            // Market User: Only get user-specific settings
            $settings = $this->settingRepository->getOnlyUserSettings($user);
        }

        // Return raw array without resource wrapper for Sanctum compatibility
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'is_admin' => $user->isAdmin(),
            'markets' => MarketResource::collection($user->markets),
            'settings' => SettingResource::collection($settings),
        ];
    }
}

