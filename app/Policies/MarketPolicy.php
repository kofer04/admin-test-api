<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Market;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MarketPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermissionTo(Permission::MarketsRead->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermissionTo(Permission::MarketsRead->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermissionTo(Permission::MarketsWrite->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Market $market): bool
    {
        $hasAccess = $user->hasAccessToMarket($market);
        return $hasAccess && $user->hasPermissionTo(Permission::MarketsWrite->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Market $market): bool
    {
        $hasAccess = $user->hasAccessToMarket($market);
        return $hasAccess && $user->hasPermissionTo(Permission::MarketsWrite->value);
    }
}
