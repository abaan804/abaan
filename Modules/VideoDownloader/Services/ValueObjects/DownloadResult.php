<?php

namespace Modules\VideoDownloader\Services\ValueObjects;

class DownloadResult
{
    public function __construct(
        public readonly bool    $success,
        public readonly ?string $filePath     = null,
        public readonly ?string $fileName     = null,
        public readonly ?int    $fileSize     = null,
        public readonly ?string $errorMessage = null,
        public readonly ?string $errorCode    = null,
    ) {
    }

    public static function success(
        string $filePath,
        string $fileName,
        int    $fileSize
    ): self {
        return new self(
            success:  true,
            filePath: $filePath,
            fileName: $fileName,
            fileSize: $fileSize,
        );
    }

    public static function failure(
        string $errorMessage,
        string $errorCode = 'DOWNLOAD_FAILED'
    ): self {
        return new self(
            success:      false,
            errorMessage: $errorMessage,
            errorCode:    $errorCode,
        );
    }

    public function failed(): bool
    {
        return ! $this->success;
    }
}