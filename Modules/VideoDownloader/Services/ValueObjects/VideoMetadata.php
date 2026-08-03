<?php

namespace Modules\VideoDownloader\Services\ValueObjects;

use Illuminate\Support\Collection;

class VideoMetadata
{
    public function __construct(
        public readonly string      $title,
        public readonly ?string     $thumbnailUrl,
        public readonly ?int        $duration,
        public readonly ?string     $uploaderName,
        public readonly ?string     $uploadDate,
        public readonly string      $platform,
        public readonly string      $originalUrl,
        public readonly Collection  $formats,
    ) {
    }

    /**
     * Build from the raw JSON output of yt-dlp --dump-json.
     */
    public static function fromYtDlp(array $data, string $originalUrl): self
    {
        // Parse formats and filter to meaningful ones
        $rawFormats = $data['formats'] ?? [];
        $formats    = collect($rawFormats)
            ->map(fn ($f) => VideoFormat::fromYtDlp($f))
            ->filter(fn (VideoFormat $f) => $f->hasVideo || $f->hasAudio)
            ->values();

        // Detect platform from extractor key
        $extractorKey = strtolower($data['extractor_key'] ?? $data['extractor'] ?? 'generic');
        $platform     = self::normalizePlatform($extractorKey);

        // Parse upload date (yt-dlp returns YYYYMMDD string)
        $uploadDate = null;
        if (! empty($data['upload_date'])) {
            try {
                $uploadDate = \Carbon\Carbon::createFromFormat('Ymd', $data['upload_date'])?->toDateString();
            } catch (\Throwable) {
                $uploadDate = null;
            }
        }

        return new self(
            title:        $data['title'] ?? 'Unknown Title',
            thumbnailUrl: $data['thumbnail'] ?? null,
            duration:     isset($data['duration']) ? (int) $data['duration'] : null,
            uploaderName: $data['uploader'] ?? $data['channel'] ?? null,
            uploadDate:   $uploadDate,
            platform:     $platform,
            originalUrl:  $originalUrl,
            formats:      $formats,
        );
    }

    protected static function normalizePlatform(string $extractor): string
    {
        return match (true) {
            str_contains($extractor, 'youtube')     => 'youtube',
            str_contains($extractor, 'twitter')     => 'twitter',
            str_contains($extractor, 'instagram')   => 'instagram',
            str_contains($extractor, 'tiktok')      => 'tiktok',
            str_contains($extractor, 'facebook')    => 'facebook',
            str_contains($extractor, 'vimeo')       => 'vimeo',
            str_contains($extractor, 'dailymotion') => 'dailymotion',
            default                                 => 'generic',
        };
    }

    /**
     * Best combined (video+audio) formats sorted by quality descending.
     */
    public function combinedFormats(): Collection
    {
        return $this->formats
            ->filter(fn (VideoFormat $f) => $f->isCombined())
            ->sortByDesc(fn (VideoFormat $f) => $f->height ?? 0)
            ->values();
    }

    /**
     * Audio-only formats.
     */
    public function audioFormats(): Collection
    {
        return $this->formats
            ->filter(fn (VideoFormat $f) => $f->isAudioOnly())
            ->values();
    }

    /**
     * Formats array as plain arrays for JSON storage in vd_formats.
     */
    public function formatsAsArray(): array
    {
        return $this->formats->map->toArray()->toArray();
    }
}