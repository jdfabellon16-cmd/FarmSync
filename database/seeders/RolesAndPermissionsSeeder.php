<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'farms.view',
            'farms.manage',
            'fields.manage',
            'activities.manage',
            'inventory.manage',
            'reports.view',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $rolePermissions = [
            'Admin' => $permissions,
            'Farm Owner' => $permissions,
            'Farm Manager/Agronomist' => ['farms.view', 'fields.manage', 'activities.manage', 'reports.view'],
            'Field Worker/Farmhand' => ['farms.view', 'activities.manage'],
            'Inventory Clerk' => ['farms.view', 'inventory.manage'],
            'Viewer' => ['farms.view', 'reports.view'],
        ];

        foreach ($rolePermissions as $roleName => $assignedPermissions) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($assignedPermissions);
        }
    }
}
