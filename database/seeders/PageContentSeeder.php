<?php

namespace Database\Seeders;

use App\Models\PageContent;
use Illuminate\Database\Seeder;

class PageContentSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            'home' => ['hero', 'intro', 'features_highlight', 'cta'],
            'about' => ['intro', 'mission', 'team'],
            'services' => ['intro'],
            'solutions' => ['intro'],
            'pricing' => ['intro'],
            'features' => ['intro'],
            'contact' => ['intro', 'address'],
            'privacy_policy' => ['body'],
            'terms_conditions' => ['body'],
            'footer' => ['column_1', 'column_2', 'column_3'],
        ];

        $order = 0;
        foreach ($sections as $page => $sectionKeys) {
            foreach ($sectionKeys as $sectionKey) {
                PageContent::firstOrCreate(
                    ['page_key' => $page, 'section_key' => $sectionKey],
                    ['sort_order' => $order++, 'is_active' => true]
                );
            }
        }
    }
}