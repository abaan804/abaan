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

        $permissions = [
            // Platform-level (super admin) permissions
            'manage companies',
            'manage packages',
            'manage subscriptions',
            'manage trial settings',
            'manage modules',
            'manage website content',
            'manage blog',
            'manage faq',
            'manage roles',
            'manage system settings',
            'manage payment gateways',
            'view activity logs',

            // Company-level permissions
            'manage company profile',
            'manage company users',
            'manage company subscription',
            'view company billing',
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

        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdmin->givePermissionTo(Permission::all());

        $companyOwner = Role::firstOrCreate(['name' => 'company-owner', 'guard_name' => 'web']);
        $companyOwner->givePermissionTo([
            'manage company profile',
            'manage company users',
            'manage company subscription',
            'view company billing',
        ]);

        $companyStaff = Role::firstOrCreate(['name' => 'company-staff', 'guard_name' => 'web']);
        $companyStaff->givePermissionTo([
            'manage company profile',
        ]);
    }
}