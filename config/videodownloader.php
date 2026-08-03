<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Download Driver
    |--------------------------------------------------------------------------
    | The driver used to fetch metadata and process downloads.
    | Swap this via VIDEO_DOWNLOAD_DRIVER to change implementation.
    |
    | Supported: "ytdlp", "mock"
    */
    'driver' => env('VIDEO_DOWNLOAD_DRIVER', 'ytdlp'),

    'drivers' => [
        'ytdlp' => \Modules\VideoDownloader\Services\YtDlpDownloadService::class,
        'mock'  => \Modules\VideoDownloader\Services\MockDownloadService::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | yt-dlp Binary
    |--------------------------------------------------------------------------
    */
    // 'ytdlp_binary' => env('YT_DLP_BINARY', '/usr/local/bin/yt-dlp'),
    'ytdlp_binary' => env('YT_DLP_BINARY', 'C:/xampp/yt-dlp/yt-dlp.exe'),
    'ffmpeg_binary' => env('FFMPEG_BINARY', 'C:/xampp/ffmpeg/bin/ffmpeg.exe'),

    /*
    |--------------------------------------------------------------------------
    | Storage
    |--------------------------------------------------------------------------
    */
    'storage' => [
        'disk'       => 'local',
        'base_path'  => 'video-downloads',
        'temp_path'  => env('VIDEO_DOWNLOAD_TEMP_PATH', 'video-downloads/temp'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Names
    |--------------------------------------------------------------------------
    */
    'queues' => [
        'metadata'  => env('VIDEO_DOWNLOAD_QUEUE_METADATA', 'metadata'),
        'downloads' => env('VIDEO_DOWNLOAD_QUEUE_DOWNLOADS', 'downloads'),
        'cleanup'   => env('VIDEO_DOWNLOAD_QUEUE_CLEANUP', 'cleanup'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Limits (overridable per company via vd_settings)
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'max_file_size_mb'        => env('VIDEO_DOWNLOAD_MAX_FILE_SIZE_MB', 500),
        'max_concurrent_downloads'=> env('VIDEO_DOWNLOAD_MAX_CONCURRENT', 3),
        'retention_days'          => env('VIDEO_DOWNLOAD_RETENTION_DAYS', 30),
        'metadata_ttl_minutes'    => env('VIDEO_DOWNLOAD_METADATA_TTL_MINUTES', 60),
        'allow_audio_only'        => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Supported Platforms
    |--------------------------------------------------------------------------
    | Used by PlatformDetector. null = allow all.
    */
    'platforms' => [
        'youtube'     => ['youtube.com', 'youtu.be', 'www.youtube.com'],
        'twitter'     => ['twitter.com', 'x.com', 'www.twitter.com'],
        'instagram'   => ['instagram.com', 'www.instagram.com'],
        'tiktok'      => ['tiktok.com', 'www.tiktok.com', 'vm.tiktok.com'],
        'facebook'    => ['facebook.com', 'www.facebook.com', 'fb.watch'],
        'vimeo'       => ['vimeo.com', 'www.vimeo.com'],
        'dailymotion' => ['dailymotion.com', 'www.dailymotion.com', 'dai.ly'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */
    'rate_limits' => [
        'url_submit'       => 10,  // per minute per user
        'metadata_fetch'   => 20,  // per minute per company
    ],

];