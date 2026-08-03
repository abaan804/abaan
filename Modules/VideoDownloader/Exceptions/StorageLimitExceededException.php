<?php

namespace Modules\VideoDownloader\Exceptions;

class StorageLimitExceededException extends \RuntimeException
{
    public function __construct(float $limitGb)
    {
        parent::__construct("Storage limit of {$limitGb} GB has been reached.");
    }
}