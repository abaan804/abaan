<?php

namespace Database\Seeders;

use App\Models\TrialSetting;
use Illuminate\Database\Seeder;

class TrialSettingSeeder extends Seeder
{
    public function run(): void
    {
        // Global trial setting (applies_to_package_id = null)
        TrialSetting::firstOrCreate(
            ['applies_to_package_id' => null],
            ['is_enabled' => true, 'duration_days' => 14]
        );
    }
}