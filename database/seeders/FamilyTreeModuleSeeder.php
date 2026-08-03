<?php

namespace Database\Seeders;

use App\Models\ModuleDefinition;
use Illuminate\Database\Seeder;

class FamilyTreeModuleSeeder extends Seeder
{
    public function run(): void
    {
        ModuleDefinition::firstOrCreate(
            ['key' => 'family-tree'],
            [
                'name_en' => 'Family Tree Manager',
                'name_ur' => 'خاندانی شجرہ نسب',
                'name_ar' => 'مدير شجرة العائلة',
                'description_en' => 'Manage complete family genealogy with multi-generation tree visualization.',
                'icon' => 'bi-diagram-3',
                'status' => 'active',
                'sort_order' => 3,
            ]
        );
    }
}