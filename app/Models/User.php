<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
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

    /* Helper Methods */
    public function isAdmin(): bool
    {
        return $this->hasRole('Super Admin');
    }

    public function accessibleMarketIds(): array
    {
        return $this->isAdmin()
            ? Market::pluck('id')->toArray()
            : $this->markets()->pluck('markets.id')->toArray();
    }
}
