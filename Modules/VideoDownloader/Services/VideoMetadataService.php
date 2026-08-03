<?php

namespace Modules\VideoDownloader\Services;

use Modules\VideoDownloader\Repositories\FormatCacheRepository;
use Modules\VideoDownloader\Services\Contracts\VideoDownloadServiceInterface;
use Modules\VideoDownloader\Services\ValueObjects\VideoMetadata;

class VideoMetadataService
{
    public function __construct(
        protected VideoDownloadServiceInterface $downloader,
        protected FormatCacheRepository        $cacheRepo,
        protected PlatformDetector             $platformDetector,
    ) {
    }

    /**
     * Fetch metadata — returns cached result if valid, otherwise
     * calls the download engine and caches the result.
     */
    public function fetch(int $companyId, string $url): VideoMetadata
    {
        // Check cache first
        $cached = $this->cacheRepo->findValid($companyId, $url);

        if ($cached) {
            // Rebuild VideoMetadata from cached data
            return $this->fromCache($cached, $url);
        }

        // Call the download engine
        $metadata = $this->downloader->fetchMetadata($url);

        // Cache it for next time
        $this->cacheRepo->store($companyId, $metadata);

        return $metadata;
    }

    /**
     * Force a fresh fetch — bypasses cache.
     */
    public function fetchFresh(int $companyId, string $url): VideoMetadata
    {
        $this->cacheRepo->invalidate($companyId, $url);
        return $this->fetch($companyId, $url);
    }

    /**
     * Rebuild a VideoMetadata value object from a VdFormat cache record.
     */
    protected function fromCache(\Modules\VideoDownloader\Models\VdFormat $cached, string $url): VideoMetadata
    {
        $formats = collect($cached->formats ?? [])
            ->map(fn ($f) => \Modules\VideoDownloader\Services\ValueObjects\VideoFormat::fromYtDlp($f))
            ->values();

        return new VideoMetadata(
            title:        $cached->video_title,
            thumbnailUrl: $cached->thumbnail_url,
            duration:     $cached->duration,
            uploaderName: $cached->uploader_name,
            uploadDate:   $cached->upload_date?->toDateString(),
            platform:     $cached->platform ?? $this->platformDetector->detectKey($url),
            originalUrl:  $url,
            formats:      $formats,
        );
    }
}