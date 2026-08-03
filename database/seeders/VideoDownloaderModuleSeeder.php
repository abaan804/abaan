<?php

namespace Database\Seeders;

use App\Models\ModuleDefinition;
use Illuminate\Database\Seeder;

class VideoDownloaderModuleSeeder extends Seeder
{
    public function run(): void
    {
        ModuleDefinition::firstOrCreate(
            ['key' => 'video-downloader'],
            [
                'name_en'        => 'Video Downloader',
                'name_ur'        => 'ویڈیو ڈاؤنلوڈر',
                'name_ar'        => 'محمل الفيديو',
                'description_en' => 'Download videos from YouTube, Twitter, TikTok, and 1000+ platforms.',
                'icon'           => 'bi-cloud-arrow-down',
                'status'         => 'active',
                'sort_order'     => 4,
            ]
        );
    }
}