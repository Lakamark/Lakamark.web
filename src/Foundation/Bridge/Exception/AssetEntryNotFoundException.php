<?php

namespace App\Foundation\Bridge\Exception;

final class AssetEntryNotFoundException extends AssetException
{
    public function __construct(
        string $entry,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf('Asset entry "%s" was not found in manifest.', $entry),
            previous: $previous
        );
    }
}
