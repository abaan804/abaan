<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class VideoDownloaderPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'videodownloader.view-dashboard',
            'videodownloader.create-download',
            'videodownloader.view-history',
            'videodownloader.delete-download',
            'videodownloader.manage-settings',
            'videodownloader.view-reports',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name'       => $permission,
                'guard_name' => 'web',
            ]);
        }

        // Grant all to company-owner by default
        $owner = Role::where('name', 'company-owner')->first();
        $owner?->givePermissionTo($permissions);
    }
}