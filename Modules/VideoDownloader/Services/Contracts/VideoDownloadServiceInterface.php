<?php

namespace Modules\VideoDownloader\Services\Contracts;

use Modules\VideoDownloader\Services\ValueObjects\DownloadResult;
use Modules\VideoDownloader\Services\ValueObjects\VideoMetadata;

interface VideoDownloadServiceInterface
{
    /**
     * Fetch video metadata and available formats for the given URL.
     *
     * @throws \Modules\VideoDownloader\Exceptions\VideoUnavailableException
     * @throws \Modules\VideoDownloader\Exceptions\UnsupportedUrlException
     */
    public function fetchMetadata(string $url): VideoMetadata;

    /**
     * Download the video in the specified format to a local temp path.
     * Returns a DownloadResult indicating success or failure.
     *
     * @param string $url      The original video URL
     * @param string $formatId The format ID selected by the user (from VideoFormat::$id)
     * @param string $destPath Absolute local path where the file should be saved
     */
    public function download(string $url, string $formatId, string $destPath): DownloadResult;

    /**
     * Check whether the given URL is supported by this driver.
     */
    public function supports(string $url): bool;
}