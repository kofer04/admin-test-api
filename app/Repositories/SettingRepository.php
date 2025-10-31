<?php

namespace App\Repositories;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SettingRepository extends Repository
{
    protected string $model = Setting::class;

    private const CACHE_KEY = 'settings';
    private const CACHE_TTL = null; // Forever

    /**
     * Get a setting value by key with optional default
     */
    public function getValue(string $key, mixed $default = null): mixed
    {
        $setting = $this->getCachedSettings()->firstWhere('key', $key);

        if (!$setting) {
            return $default;
        }

        return $setting->typed_value;
    }

    /**
     * Set a setting value (creates or updates)
     */
    public function set(string $key, mixed $value, ?string $type = null, ?string $description = null): Setting
    {
        $setting = Setting::byKey($key)->first();

        if ($setting) {
            // Update existing
            if ($type === null) {
                $setting->setTypedValue($value);
            } else {
                $setting->type = $type;
                $setting->value = (string) $value;
            }

            if ($description !== null) {
                $setting->description = $description;
            }

            $setting->save();
        } else {
            // Create new
            $setting = new Setting(['key' => $key, 'description' => $description]);

            if ($type === null) {
                $setting->setTypedValue($value);
            } else {
                $setting->type = $type;
                $setting->value = (string) $value;
            }

            $setting->save();
        }

        // Clear cache after update
        $this->clearCache();

        return $setting;
    }

    /**
     * Get all settings with keys starting with a prefix
     * Returns collection keyed by setting key
     */
    public function getGroup(string $prefix): Collection
    {
        return $this->getCachedSettings()
            ->filter(fn($setting) => str_starts_with($setting->key, $prefix))
            ->mapWithKeys(fn($setting) => [$setting->key => $setting->typed_value]);
    }

    /**
     * Get all settings as key-value pairs
     */
    public function all(): Collection
    {
        return $this->getCachedSettings()
            ->mapWithKeys(fn($setting) => [$setting->key => $setting->typed_value]);
    }

    /**
     * Check if a setting exists
     */
    public function has(string $key): bool
    {
        return $this->getCachedSettings()->contains('key', $key);
    }

    /**
     * Delete a setting by key
     */
    public function forget(string $key): bool
    {
        $setting = Setting::byKey($key)->first();

        if ($setting) {
            $setting->delete();
            $this->clearCache();
            return true;
        }

        return false;
    }

    /**
     * Get cached settings collection
     */
    private function getCachedSettings(): Collection
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return Setting::all();
        });
    }

    /**
     * Clear the settings cache
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Set a setting for a specific user (creates or updates)
     */
    public function setForUser(User $user, string $key, mixed $value, ?string $description = null): Setting
    {
        $setting = Setting::forUser($user)->where('key', $key)->first();

        if ($setting) {
            // Update existing user setting
            $setting->setTypedValue($value);
            if ($description !== null) {
                $setting->description = $description;
            }
            $setting->save();
        } else {
            // Create new user setting
            $setting = new Setting(['key' => $key, 'description' => $description]);
            $setting->setTypedValue($value);
            $setting->owner()->associate($user);
            $setting->save();
        }

        $this->clearCache();

        return $setting;
    }

    /**
     * Get a user setting value by key with optional default
     * Falls back to system-wide setting if user setting doesn't exist
     */
    public function getForUser(User $user, string $key, mixed $default = null): mixed
    {
        // First, try to get user-specific setting
        $userSetting = Setting::forUser($user)->where('key', $key)->first();
        
        if ($userSetting) {
            return $userSetting->typed_value;
        }

        // Fall back to system-wide setting
        $systemSetting = Setting::systemWide()->where('key', $key)->first();
        
        return $systemSetting ? $systemSetting->typed_value : $default;
    }

    /**
     * Get all settings for a user (merges system-wide with user-specific)
     * User-specific settings override system-wide settings
     */
    public function getAllForUser(User $user): Collection
    {
        $systemSettings = Setting::systemWide()->get();
        $userSettings = Setting::forUser($user)->get();

        // Start with system-wide settings
        $merged = $systemSettings->keyBy('key');

        // Override with user-specific settings
        foreach ($userSettings as $userSetting) {
            $merged->put($userSetting->key, $userSetting);
        }

        return $merged->values();
    }

    /**
     * Get only user-specific settings (no system-wide)
     */
    public function getOnlyUserSettings(User $user): Collection
    {
        return Setting::forUser($user)->get();
    }
}

