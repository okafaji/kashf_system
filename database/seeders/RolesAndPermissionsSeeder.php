<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = config('permissions.permissions', []);

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $roleConfig = config('permissions.roles', []);

        foreach ($roleConfig as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);

            if ($rolePermissions === '*') {
                $role->syncPermissions(Permission::all());
                continue;
            }

            $role->syncPermissions($rolePermissions);
        }

        $this->command->info('Roles and permissions created successfully!');
    }
}
