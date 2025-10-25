<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
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

    /* Helpers / Accessors */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function accessibleMarketIds(): array
    {
        return $this->isAdmin()
            ? Market::pluck('id')->toArray()
            : $this->markets()->pluck('id')->toArray();
    }
}
