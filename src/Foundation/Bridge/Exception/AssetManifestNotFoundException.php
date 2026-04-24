<?php

namespace App\Foundation\Bridge\Exception;

final class AssetManifestNotFoundException extends AssetException
{
    public function __construct(
        string $path,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf('Asset manifest not found at path: "%s".', $path),
            previous: $previous
        );
    }
}
