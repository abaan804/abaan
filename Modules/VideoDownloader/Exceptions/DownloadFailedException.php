<?php

namespace Modules\VideoDownloader\Exceptions;

class DownloadFailedException extends \RuntimeException
{
    public function __construct(
        string $message,
        public readonly string $errorCode = 'DOWNLOAD_FAILED'
    ) {
        parent::__construct($message);
    }
}