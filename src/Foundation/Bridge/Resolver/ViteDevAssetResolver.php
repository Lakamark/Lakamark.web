<?php

namespace App\Foundation\Bridge\Resolver;

use App\Foundation\Bridge\Contract\AssetResolverInterface;

/**
 * Resolves Vite assets from the development server.
 *
 * This resolver is used only in the dev environment. It does not read the Vite
 * manifest because Vite serves source files directly through the dev server.
 */
final readonly class ViteDevAssetResolver implements AssetResolverInterface
{
    public function __construct(
        private string $devServerUrl,
        private string $publicBasePath,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function resolve(string $entry): array
    {
        $baseUrl = rtrim($this->devServerUrl, '/');
        $basePath = '/'.trim($this->publicBasePath, '/');
        $entryPath = ltrim($entry, '/');

        return [
            'vite_client' => $baseUrl.$basePath.'/@vite/client',
            'file' => $baseUrl.$basePath.'/'.$entryPath,
            'css' => [],
            'imports' => [],
        ];
    }
}
