<?php

namespace Modules\VideoDownloader\Repositories;

use Modules\VideoDownloader\Models\VdFormat;
use Modules\VideoDownloader\Services\ValueObjects\VideoMetadata;

class FormatCacheRepository
{
    /**
     * Find a valid (non-expired) cache entry for the given URL.
     */
    public function findValid(int $companyId, string $url): ?VdFormat
    {
        $hash = VdFormat::hashUrl($url);

        return VdFormat::where('company_id', $companyId)
            ->where('url_hash', $hash)
            ->valid()
            ->first();
    }

    /**
     * Store or update a metadata cache entry.
     */
    public function store(int $companyId, VideoMetadata $metadata): VdFormat
    {
        $hash    = VdFormat::hashUrl($metadata->originalUrl);
        $ttl     = config('videodownloader.defaults.metadata_ttl_minutes', 60);

        return VdFormat::updateOrCreate(
            [
                'company_id' => $companyId,
                'url_hash'   => $hash,
            ],
            [
                'original_url'  => $metadata->originalUrl,
                'platform'      => $metadata->platform,
                'video_title'   => $metadata->title,
                'thumbnail_url' => $metadata->thumbnailUrl,
                'duration'      => $metadata->duration,
                'uploader_name' => $metadata->uploaderName,
                'upload_date'   => $metadata->uploadDate,
                'formats'       => $metadata->formatsAsArray(),
                'fetched_at'    => now(),
                'expires_at'    => now()->addMinutes($ttl),
            ]
        );
    }

    /**
     * Delete all expired cache entries.
     * Called by CleanupExpiredFormatsJob.
     */
    public function deleteExpired(): int
    {
        return VdFormat::expired()->delete();
    }

    /**
     * Invalidate a specific URL's cache entry for a company.
     */
    public function invalidate(int $companyId, string $url): void
    {
        $hash = VdFormat::hashUrl($url);
        VdFormat::where('company_id', $companyId)->where('url_hash', $hash)->delete();
    }
}