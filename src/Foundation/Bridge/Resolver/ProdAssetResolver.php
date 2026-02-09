<?php

namespace App\Foundation\Bridge\Resolver;

use App\Foundation\Bridge\Contract\AssetResolverInterface;
use App\Foundation\Bridge\Contract\EntryMapperInterface;
use App\Foundation\Bridge\ManifestReader;
use Psr\Cache\InvalidArgumentException;

final readonly class ProdAssetResolver implements AssetResolverInterface
{
    public function __construct(
        private ManifestReader $manifestReader,
        private EntryMapperInterface $entryMapper,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function resolveJs(string $entry): ?string
    {
        $manifest = $this->manifestReader->read();
        $key = $this->entryMapper->map($entry);

        $file = $manifest[$key]['file'] ?? $manifest[$key] ?? null;
        if (!is_string($file) || '' === $file) {
            return null;
        }

        return $this->join($file);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function resolveCss(string $entry): ?string
    {
        $manifest = $this->manifestReader->read();
        $key = $this->entryMapper->map($entry); // In dev, we reed the CSS file from the js file.

        $css = $manifest[$key]['css'][0] ?? null;
        if (!is_string($css) || '' === $css) {
            return null;
        }

        return $this->join($css);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function resolveImports(string $entry): array
    {
        $manifest = $this->manifestReader->read();
        $key = $this->entryMapper->map($entry); // ex: "assets/app.js"

        $imports = $manifest[$key]['imports'] ?? [];
        if (!is_array($imports)) {
            return [];
        }

        $out = [];
        foreach ($imports as $importKey) {
            if (!is_string($importKey) || '' === $importKey) {
                continue;
            }
            $file = $manifest[$importKey]['file'] ?? null;
            if (is_string($file) && '' !== $file) {
                $out[] = $this->join($file);
            }
        }

        return $out;
    }

    private function join(string $path): string
    {
        return '/'.ltrim($path, '/');
    }
}
