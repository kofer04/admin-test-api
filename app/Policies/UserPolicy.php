<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     * Super Admin or users with users:read permission.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermissionTo('users:read');
    }

    /**
     * Determine whether the user can view a specific user.
     * Super Admin or users with users:read permission.
     * Users can also view their own profile.
     */
    public function view(User $user, User $model): bool
    {
        // Users can always view their own profile
        if ($user->id === $model->id) {
            return true;
        }

        return $user->isAdmin() || $user->hasPermissionTo('users:read');
    }

    /**
     * Determine whether the user can create models.
     * Super Admin or users with users:write permission.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermissionTo('users:write');
    }

    /**
     * Determine whether the user can update the model.
     * Super Admin or users with users:write permission.
     * Users can also update their own profile (with restrictions).
     */
    public function update(User $user, User $model): bool
    {
        // Users can update their own profile
        if ($user->id === $model->id) {
            return true;
        }

        return $user->isAdmin() || $user->hasPermissionTo('users:write');
    }

    /**
     * Determine whether the user can delete the model.
     * Only Super Admin or users with users:write permission.
     * Users cannot delete themselves.
     */
    public function delete(User $user, User $model): bool
    {
        // Users cannot delete themselves
        if ($user->id === $model->id) {
            return false;
        }

        return $user->isAdmin() || $user->hasPermissionTo('users:write');
    }

    /**
     * Determine whether the user can restore the model.
     * Only Super Admin or users with users:write permission.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->isAdmin() || $user->hasPermissionTo('users:write');
    }

    /**
     * Determine whether the user can permanently delete the model.
     * Only Super Admin.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $user->isAdmin();
    }
}
