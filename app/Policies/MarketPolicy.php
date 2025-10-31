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
     * Users must have read permission to access the markets list.
     * Super Admin always has access.
     * Regular users with read permission will see filtered results (handled by repository).
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermissionTo(Permission::MarketsRead->value);
    }

    /**
     * Determine whether the user can view a specific market.
     * Users can view a market if they have read permission AND access to that market.
     */
    public function view(User $user, Market $market): bool
    {
        if (!$user->hasPermissionTo(Permission::MarketsRead->value) && !$user->isAdmin()) {
            return false;
        }

        return $user->hasAccessToMarket($market);
    }

    /**
     * Determine whether the user can create models.
     * Only Super Admin can create markets.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     * Users can update if they have write permission AND access to the specific market.
     */
    public function update(User $user, Market $market): bool
    {
        if (!$user->hasPermissionTo(Permission::MarketsWrite->value) && !$user->isAdmin()) {
            return false;
        }

        return $user->hasAccessToMarket($market);
    }

    /**
     * Determine whether the user can delete the model.
     * Only Super Admin can delete markets.
     */
    public function delete(User $user, Market $market): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     * Only Super Admin can restore markets.
     */
    public function restore(User $user, Market $market): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     * Only Super Admin can force delete markets.
     */
    public function forceDelete(User $user, Market $market): bool
    {
        return $user->isAdmin();
    }
}
