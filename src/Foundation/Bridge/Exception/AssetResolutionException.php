<?php

namespace App\Foundation\Bridge\Exception;

final class AssetResolutionException extends AssetException
{
    public function __construct(
        string $message,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf('Asset resolution failed: %s', $message),
            previous: $previous
        );
    }
}
