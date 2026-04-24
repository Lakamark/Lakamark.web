<?php

namespace App\Foundation\Bridge\Resolver;

use App\Foundation\Bridge\Contract\AssetResolverInterface;

/**
 * Selects the proper asset resolver based on the Symfony environment.
 *
 * In dev, assets are resolved through the Vite development server.
 * In prod, assets are resolved from the compiled Vite manifest.
 */
final readonly class EnvironmentAssetResolver implements AssetResolverInterface
{
    public function __construct(
        private string $environment,
        private ViteDevAssetResolver $devResolver,
        private ManifestAssetResolver $prodResolver,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function resolve(string $entry): array
    {
        if ('dev' === $this->environment) {
            return $this->devResolver->resolve($entry);
        }

        return $this->prodResolver->resolve($entry);
    }
}
