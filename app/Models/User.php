<?php

namespace App\Models;

use App\Models\Market;
use App\Models\Setting;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use SoftDeletes;
    use HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /* Relationships */
    public function markets()
    {
        return $this->belongsToMany(Market::class, 'market_user')
                    ->withTimestamps();
    }

    public function settings()
    {
        return $this->morphMany(Setting::class, 'owner', 'owner_type', 'owner_id');
    }

    /* Helper Methods */
    public function isAdmin(): bool
    {
        return $this->hasRole('Super Admin');
    }

    /**
     * Get accessible market IDs for this user
     * Cached to avoid repeated queries
     */
    public function accessibleMarketIds(): array
    {
        // Use cached result from withMarketIds scope if available
        if (isset($this->attributes['market_ids'])) {
            return json_decode($this->attributes['market_ids'], true) ?? [];
        }

        if ($this->isAdmin()) {
            $marketIds = Market::pluck('id')->toArray();
            return $marketIds;
        } else {
            $marketIds = $this->markets()->pluck('markets.id')->toArray();
            return $marketIds;
        }
    }


    /**
     * Check if the user has access to the market
     */
    public function hasAccessToMarket(Market|int|array $market): bool
    {
        return match(true) {
            $market instanceof Market => $this->isAdmin() || in_array($market->id, $this->accessibleMarketIds()),
            is_array($market) => $this->isAdmin() || count(array_intersect($market, $this->accessibleMarketIds())) > 0,
            is_int($market) => $this->isAdmin() || in_array($market, $this->accessibleMarketIds()),
            default => throw new \InvalidArgumentException('Invalid market type'),
        };
    }

    /**
     * Get a user setting value by key with optional default
     * Falls back to system-wide setting if user setting doesn't exist
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        // First, try to get user-specific setting
        $userSetting = $this->settings()->where('key', $key)->first();
        
        if ($userSetting) {
            return $userSetting->typed_value;
        }

        // Fall back to system-wide setting
        $systemSetting = Setting::systemWide()->where('key', $key)->first();
        
        return $systemSetting ? $systemSetting->typed_value : $default;
    }

    /**
     * Set a user setting value (creates or updates)
     */
    public function setSetting(string $key, mixed $value): Setting
    {
        $setting = $this->settings()->where('key', $key)->first();

        if ($setting) {
            // Update existing
            $setting->setTypedValue($value);
            $setting->save();
        } else {
            // Create new
            $setting = new Setting(['key' => $key]);
            $setting->setTypedValue($value);
            $setting->owner()->associate($this);
            $setting->save();
        }

        return $setting;
    }
}
