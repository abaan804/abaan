<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class FamilyTreePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'familytree.view-dashboard',
            'familytree.manage-families',
            'familytree.manage-members',
            'familytree.manage-relationships',
            'familytree.manage-events',
            'familytree.manage-documents',
            'familytree.view-reports',
            'familytree.view-tree',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $owner = Role::where('name', 'company-owner')->first();
        $owner?->givePermissionTo($permissions);
    }
}