<?php

namespace Modules\VideoDownloader\Services\ValueObjects;

class Platform
{
    public function __construct(
        public readonly string $key,
        public readonly string $label,
        public readonly string $icon,
        public readonly bool   $supported,
    ) {
    }

    public static function make(string $key): self
    {
        $platforms = [
            'youtube'     => ['YouTube',     'bi-youtube',    true],
            'twitter'     => ['Twitter / X', 'bi-twitter-x',  true],
            'instagram'   => ['Instagram',   'bi-instagram',  true],
            'tiktok'      => ['TikTok',      'bi-tiktok',     true],
            'facebook'    => ['Facebook',    'bi-facebook',   true],
            'vimeo'       => ['Vimeo',       'bi-vimeo',      true],
            'dailymotion' => ['Dailymotion', 'bi-play-circle',true],
            'generic'     => ['Other',       'bi-globe',      true],
        ];

        [$label, $icon, $supported] = $platforms[$key] ?? ['Unknown', 'bi-globe', false];

        return new self($key, $label, $icon, $supported);
    }

    public function isSupported(): bool
    {
        return $this->supported;
    }
}