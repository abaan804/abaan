<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class LedgerPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'easykhata.view-dashboard',
            'easykhata.manage-customers',
            'easykhata.manage-suppliers',
            'easykhata.manage-transactions',
            'easykhata.manage-categories',
            'easykhata.view-reports',
            'easykhata.manage-reminders',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // company-owner gets full access to every module by default.
        $owner = Role::where('name', 'company-owner')->first();
        $owner?->givePermissionTo($permissions);

        // company-staff gets none by default — owner grants individually via Team Management.
    }
}