<?php

namespace App\Foundation\Bridge\Exception;

final class AssetManifestInvalidException extends AssetException
{
    public function __construct(
        string $reason = 'Invalid manifest structure.',
        ?\Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf('Asset manifest is invalid: %s', $reason),
            previous: $previous
        );
    }
}
