<?php

namespace App\Repositories;

use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SettingsRepository extends Repository
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
}

