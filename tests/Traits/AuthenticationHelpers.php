<?php

namespace Tests\Traits;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

trait AuthenticationHelpers
{
    /**
     * Create and authenticate a user with cookie-based auth
     */
    protected function authenticateUser(User $user): self
    {
        $this->actingAs($user, 'web');
        
        return $this;
    }

    /**
     * Perform login request with CSRF protection
     */
    protected function loginWithCredentials(string $email, string $password): self
    {
        // Get CSRF cookie first
        $this->get('/sanctum/csrf-cookie');
        
        // Perform login
        $this->post('/login', [
            'email' => $email,
            'password' => $password,
        ]);
        
        return $this;
    }

    /**
     * Perform logout request
     */
    protected function logoutUser(): self
    {
        $this->post('/logout');
        
        return $this;
    }

    /**
     * Create a Super Admin user
     */
    protected function createSuperAdmin(array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'name' => 'Super Admin',
            'email' => 'superadmin@test.com',
            'password' => Hash::make('password'),
        ], $attributes));

        $this->ensureRoleExists('Super Admin');
        $user->assignRole('Super Admin');

        return $user;
    }

    /**
     * Create a Market User with specific permissions
     */
    protected function createMarketUser(array $attributes = [], array $permissions = []): User
    {
        $user = User::factory()->create(array_merge([
            'name' => 'Market User',
            'email' => 'marketuser@test.com',
            'password' => Hash::make('password'),
        ], $attributes));

        $this->ensureRoleExists('Market User', $permissions);
        $user->assignRole('Market User');

        return $user;
    }

    /**
     * Create a user without any role or permissions
     */
    protected function createGuestUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'name' => 'Guest User',
            'email' => 'guest@test.com',
            'password' => Hash::make('password'),
        ], $attributes));
    }

    /**
     * Create a user with specific permissions
     */
    protected function createUserWithPermissions(array $permissions, array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'name' => 'Custom User',
            'email' => 'custom@test.com',
            'password' => Hash::make('password'),
        ], $attributes));

        foreach ($permissions as $permission) {
            $user->givePermissionTo($permission);
        }

        return $user;
    }

    /**
     * Ensure role exists with given permissions
     */
    protected function ensureRoleExists(string $roleName, array $permissions = []): Role
    {
        $role = Role::firstOrCreate(['name' => $roleName]);
        
        if (!empty($permissions)) {
            // Create permissions if they don't exist
            foreach ($permissions as $permission) {
                \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permission]);
            }
            
            $role->syncPermissions($permissions);
        }

        return $role;
    }

}

