<?php

namespace Modules\VideoDownloader\Exceptions;

class UnsupportedUrlException extends \RuntimeException
{
    public function __construct(string $url)
    {
        parent::__construct("URL is not supported: [{$url}]");
    }
}