<?php

namespace Database\Seeders;

use App\Models\ModuleDefinition;
use Illuminate\Database\Seeder;

class ModuleDefinitionSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            ['key' => 'ledger', 'name_en' => 'Ledger Management', 'icon' => 'bi-journal-bookmark', 'sort_order' => 1],
            ['key' => 'pos', 'name_en' => 'POS System', 'icon' => 'bi-cash-coin', 'sort_order' => 2],
            ['key' => 'school', 'name_en' => 'School Management', 'icon' => 'bi-mortarboard', 'sort_order' => 3],
            ['key' => 'clinic', 'name_en' => 'Clinic Management', 'icon' => 'bi-heart-pulse', 'sort_order' => 4],
            ['key' => 'hr', 'name_en' => 'HR Management', 'icon' => 'bi-people', 'sort_order' => 5],
            ['key' => 'crm', 'name_en' => 'CRM System', 'icon' => 'bi-person-lines-fill', 'sort_order' => 6],
        ];

        foreach ($modules as $module) {
            ModuleDefinition::firstOrCreate(
                ['key' => $module['key']],
                array_merge($module, ['status' => 'coming_soon'])
            );
        }
    }
}