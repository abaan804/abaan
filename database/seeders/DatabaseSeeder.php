<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            PackageSeeder::class,
            TrialSettingSeeder::class,
            SystemSettingsSeeder::class,
            ModuleDefinitionSeeder::class,
            PageContentSeeder::class,
            LedgerPermissionSeeder::class,
            MasjidPermissionSeeder::class,
            MasjidModuleSeeder::class,
            FamilyTreePermissionSeeder::class,
            FamilyTreeModuleSeeder::class,
            VideoDownloaderPermissionSeeder::class,
            VideoDownloaderModuleSeeder::class,

        ]);
    }
}