<?php

namespace App\Foundation\Bridge\Resolver;

use App\Foundation\Bridge\Contract\AssetResolverInterface;
use App\Foundation\Bridge\Contract\ManifestReaderInterface;
use App\Foundation\Bridge\Exception\AssetEntryNotFoundException;

/**
 * Resolves asset entries from a Vite manifest.
 *
 * This resolver:
 * - retrieves the manifest via ManifestReader
 * - looks up a specific entry
 * - returns raw entry data
 *
 * It does not:
 * - read files directly
 * - perform fallback logic
 * - format output for Twig
 */
final readonly class ManifestAssetResolver implements AssetResolverInterface
{
    public function __construct(
        private readonly ManifestReaderInterface $reader,
    ) {
    }

    public function resolve(string $entry): array
    {
        $manifest = $this->reader->read();

        if (!array_key_exists($entry, $manifest)) {
            throw new AssetEntryNotFoundException($entry);
        }

        return $manifest[$entry];
    }
}
