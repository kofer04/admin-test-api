<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
        'owner_id',
        'owner_type',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the owner of the setting (polymorphic relationship)
     */
    public function owner()
    {
        return $this->morphTo('owner', 'owner_type', 'owner_id');
    }

    /**
     * Get the value with proper type casting
     */
    public function getTypedValueAttribute(): mixed
    {
        return match($this->type) {
            'integer' => (int) $this->value,
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($this->value, true),
            'float', 'double' => (float) $this->value,
            default => $this->value,
        };
    }

    /**
     * Set the value with automatic type detection
     */
    public function setTypedValue(mixed $value): void
    {
        if (is_bool($value)) {
            $this->type = 'boolean';
            $this->value = $value ? '1' : '0';
        } elseif (is_int($value)) {
            $this->type = 'integer';
            $this->value = (string) $value;
        } elseif (is_float($value)) {
            $this->type = 'float';
            $this->value = (string) $value;
        } elseif (is_array($value) || is_object($value)) {
            $this->type = 'json';
            $this->value = json_encode($value);
        } else {
            $this->type = 'string';
            $this->value = (string) $value;
        }
    }

    /**
     * Scope to filter by key
     */
    public function scopeByKey($query, string $key)
    {
        return $query->where('key', $key);
    }

    /**
     * Scope to filter by key prefix (for grouped settings)
     */
    public function scopeByGroup($query, string $prefix)
    {
        return $query->where('key', 'LIKE', $prefix . '%');
    }

    /**
     * Scope to filter settings for a specific user
     */
    public function scopeForUser($query, User $user)
    {
        return $query->where('owner_type', User::class)
                     ->where('owner_id', $user->id);
    }

    /**
     * Scope to filter system-wide settings (no owner)
     */
    public function scopeSystemWide($query)
    {
        return $query->whereNull('owner_id')
                     ->whereNull('owner_type');
    }
}

