<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $rolesPermissions = config('roles-and-permissions.roles', []);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create or update permissions
        $allPermissions = collect($rolesPermissions)->flatten()->unique();
        foreach ($allPermissions as $permissionName) {
            Permission::updateOrCreate(['name' => $permissionName]);
        }

        // Create or update roles and sync permissions
        foreach ($rolesPermissions as $roleName => $permissions) {
            $role = Role::updateOrCreate(['name' => $roleName]);
            $role->syncPermissions($permissions);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command?->info('✅ Roles and permissions have been synced successfully.');
    }
}
