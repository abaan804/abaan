<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class MasjidPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'masjid.view-dashboard',
            'masjid.manage-mosque-profile',
            'masjid.manage-members',
            'masjid.manage-seasons',
            'masjid.manage-payments',
            'masjid.view-reports',
            'masjid.manage-settings',
            'masjid.send-notifications',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $owner = Role::where('name', 'company-owner')->first();
        $owner?->givePermissionTo($permissions);
    }
}