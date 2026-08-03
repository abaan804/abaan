<?php

namespace Modules\VideoDownloader\Services;

use Modules\VideoDownloader\Services\ValueObjects\Platform;

class PlatformDetector
{
    /**
     * Map of platform key → list of matching hostnames.
     */
    protected array $map;

    public function __construct()
    {
        $this->map = config('videodownloader.platforms', []);
    }

    /**
     * Detect the platform from a URL string.
     * Returns a Platform value object.
     */
    public function detect(string $url): Platform
    {
        $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');
        // Strip leading www.
        $host = preg_replace('/^www\./', '', $host);

        foreach ($this->map as $platformKey => $domains) {
            foreach ($domains as $domain) {
                $domain = strtolower(preg_replace('/^www\./', '', $domain));
                if ($host === $domain || str_ends_with($host, '.' . $domain)) {
                    return Platform::make($platformKey);
                }
            }
        }

        return Platform::make('generic');
    }

    /**
     * Return just the platform key string.
     */
    public function detectKey(string $url): string
    {
        return $this->detect($url)->key;
    }

    /**
     * Whether a URL points to a known supported platform.
     */
    public function isSupported(string $url): bool
    {
        return $this->detect($url)->isSupported();
    }
}