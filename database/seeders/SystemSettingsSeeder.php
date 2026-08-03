<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SystemSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'email_verification_enabled' => false,
            'currency_symbol' => '$',
            'currency_code' => 'USD',
            'currency_position' => 'before', // before | after
            'date_format' => 'M d, Y',
        ];

        foreach ($defaults as $key => $value) {
            Setting::firstOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => 'general']
            );
        }
    }
}