<?php

namespace Database\Seeders;

use App\Models\ModuleDefinition;
use Illuminate\Database\Seeder;

class MasjidModuleSeeder extends Seeder
{
    public function run(): void
    {
        ModuleDefinition::firstOrCreate(
            ['key' => 'masjid'],
            [
                'name_en' => 'Masjid Contribution Manager',
                'name_ur' => 'مسجد چندہ منیجر',
                'name_ar' => 'مدير مساهمات المسجد',
                'description_en' => 'Manage mosque member contributions, seasonal payments, balances, and reminders.',
                'icon' => 'bi-building',
                'status' => 'active',
                'sort_order' => 2,
            ]
        );
    }
}