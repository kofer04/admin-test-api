<?php

namespace App\Http\Controllers\Api\V1;

use App\DTO\SettingParamsDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Setting\UpdateSettingRequest;
use App\Http\Resources\SettingResource;
use App\Repositories\SettingRepository;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingController extends Controller
{
    public function __construct(
        protected SettingRepository $settingRepository,
        protected SettingService $settingService
    ) {}

    /**
     * Display a listing of the user's settings.
     * - Market Users: Only return user-specific settings
     * - Super Admin: Return merged system-wide + user-specific settings
     */
    public function index(Request $request): JsonResource
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            // Super Admin: Get all settings (system-wide + user-specific merged)
            $settings = $this->settingRepository->getAllForUser($user);
        } else {
            // Market User: Only get user-specific settings
            $settings = $this->settingRepository->getOnlyUserSettings($user);
        }

        return SettingResource::collection($settings);
    }

    /**
     * Update a setting for the authenticated user.
     */
    public function update(UpdateSettingRequest $request): JsonResource
    {
        $user = $request->user();

        $dto = SettingParamsDTO::fromRequest($request, $user);

        $setting = $this->settingService->saveSetting($dto, $user);

        return new SettingResource($setting);
    }
}

