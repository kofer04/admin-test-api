<?php

namespace App\Services;

use App\DTO\SettingParamsDTO;
use App\Models\Market;
use App\Models\Setting;
use App\Models\User;
use App\Repositories\SettingRepository;

class SettingService
{
    public function __construct(
        private SettingRepository $settingRepository
    ) {}

    /**
     * Save a setting for a user
     * Delegates to specific handlers based on the setting key
     */
    public function saveSetting(SettingParamsDTO $dto, User $user): Setting
    {
        // Handle specific setting types
        $value = match($dto->key) {
            'selected_markets' => $this->saveSelectedMarkets($dto->value, $user),
            default => $dto->value,
        };

        return $this->settingRepository->setForUser(
            user: $user,
            key: $dto->key,
            value: $value
        );
    }

    /**
     * Save selected markets setting with sanitization
     * Validates market access based on user role
     */
    private function saveSelectedMarkets(mixed $marketIds, User $user): array
    {
        // Ensure we have an array
        if (!is_array($marketIds)) {
            $marketIds = [];
        }

        // If no market IDs provided, return empty array
        if (empty($marketIds)) {
            return [];
        }

        if ($user->isAdmin()) {
            // Super Admin: validate markets exist but don't restrict access
            return Market::whereIn('id', $marketIds)
                ->pluck('id')
                ->toArray();
        }
        
        // Regular user: only allow accessible markets
        $accessibleIds = $user->accessibleMarketIds();
        
        // Filter to only markets the user has access to
        $sanitizedIds = array_intersect($marketIds, $accessibleIds);
        
        // Validate that the sanitized IDs actually exist in the database
        return Market::whereIn('id', $sanitizedIds)
            ->pluck('id')
            ->toArray();
    }
}

