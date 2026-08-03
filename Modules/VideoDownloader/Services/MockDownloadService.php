<?php

namespace Modules\VideoDownloader\Services;

use Illuminate\Support\Collection;
use Modules\VideoDownloader\Services\Contracts\VideoDownloadServiceInterface;
use Modules\VideoDownloader\Services\ValueObjects\DownloadResult;
use Modules\VideoDownloader\Services\ValueObjects\VideoFormat;
use Modules\VideoDownloader\Services\ValueObjects\VideoMetadata;

class MockDownloadService implements VideoDownloadServiceInterface
{
    public function fetchMetadata(string $url): VideoMetadata
    {
        // Simulate a small delay
        sleep(1);

        $formats = collect([
            new VideoFormat('137+140', 'mp4', '1080p',  1080, 1920, 30, 52428800,  'avc1',  'mp4a', true,  true,  'Full HD'),
            new VideoFormat('136+140', 'mp4', '720p',    720, 1280, 30, 31457280,  'avc1',  'mp4a', true,  true,  'HD'),
            new VideoFormat('135+140', 'mp4', '480p',    480, 854,  30, 15728640,  'avc1',  'mp4a', true,  true,  'SD'),
            new VideoFormat('134+140', 'mp4', '360p',    360, 640,  30, 7340032,   'avc1',  'mp4a', true,  true,  'Low'),
            new VideoFormat('140',     'm4a', '128kbps', null,null,  null, 5242880, null,    'mp4a', false, true,  'Audio only'),
        ]);

        return new VideoMetadata(
            title:        'Sample Video — Mock Download Service',
            thumbnailUrl: 'https://picsum.photos/seed/video/480/270',
            duration:     227,
            uploaderName: 'Mock Uploader',
            uploadDate:   '2024-01-15',
            platform:     'youtube',
            originalUrl:  $url,
            formats:      $formats,
        );
    }

    public function download(string $url, string $formatId, string $destPath): DownloadResult
    {
        // Simulate download time
        sleep(3);

        // Ensure directory exists
        $dir = dirname($destPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Write a small dummy file
        $content  = str_repeat('MOCK VIDEO CONTENT - FOR TESTING ONLY ', 100);
        $filePath = $destPath . '.mp4';
        file_put_contents($filePath, $content);

        return DownloadResult::success(
            filePath: $filePath,
            fileName: 'mock-video.mp4',
            fileSize: strlen($content),
        );
    }

    public function supports(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
}