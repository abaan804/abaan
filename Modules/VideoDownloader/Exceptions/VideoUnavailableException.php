<?php

namespace Modules\VideoDownloader\Exceptions;

class VideoUnavailableException extends \RuntimeException
{
    public function __construct(string $url, string $reason = '')
    {
        parent::__construct(
            "Video unavailable at [{$url}]" . ($reason ? ": {$reason}" : '.')
        );
    }
}