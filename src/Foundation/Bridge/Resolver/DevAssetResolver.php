<?php

namespace App\Foundation\Bridge\Resolver;

use App\Foundation\Bridge\Contract\AssetResolverInterface;
use App\Foundation\Bridge\Contract\EntryMapperInterface;

final readonly class DevAssetResolver implements AssetResolverInterface
{
    /** ex: "https://localhost:3000" ou "https://myhost:3000" */
    public function __construct(
        private string $devServerOrigin,
        private EntryMapperInterface $entryMapper,
        private string $assetsPrefix = '/assets',
    ) {
    }

    public function resolveJs(string $entry): ?string
    {
        $mappedEntry = $this->entryMapper->map($entry);

        return rtrim($this->devServerOrigin, '/')
            .rtrim($this->assetsPrefix, '/')
            .'/'
            .ltrim($mappedEntry, '/');
    }

    public function resolveCss(string $entry): ?string
    {
        // In dev, we reed the CSS file in from the js.
        return null;
    }

    public function resolveImports(string $entry): array
    {
        return [];
    }
}
