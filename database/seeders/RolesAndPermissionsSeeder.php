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

        $allConfigPermissions = collect($rolesPermissions)->flatten()->unique();
        $allConfigRoles = collect($rolesPermissions)->keys();

        Permission::whereNotIn('name', $allConfigPermissions)->delete();

        Role::whereNotIn('name', $allConfigRoles)->delete();

        foreach ($rolesPermissions as $roleName => $permissions) {
            foreach ($permissions as $permissionName) {
                Permission::updateOrCreate(['name' => $permissionName]);
            }

            $role = Role::updateOrCreate(['name' => $roleName]);
            $role->syncPermissions($permissions);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command?->info('✅ Roles and permissions have been synced successfully.');
    }
}
