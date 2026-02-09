<?php

namespace App\Foundation\Bridge\Resolver;

use App\Foundation\Bridge\Contract\AssetResolverInterface;

/**
 * Null implementation of AssetResolverInterface.
 *
 * This resolver is intended to be used in the `test` environment.
 * It deliberately resolves no assets and returns empty values.
 *
 * Purpose:
 * - Allow the Symfony container to compile in tests without requiring
 *   Vite, a manifest file, or a dev server.
 * - Ensure backend / domain tests remain completely decoupled
 *   from frontend asset resolution concerns.
 *
 * Behavior:
 * - No JavaScript is resolved.
 * - No CSS is resolved.
 * - No imports are returned.
 *
 * This follows the Null Object pattern and avoids conditionals
 * or environment checks inside the AssetBridge.
 *
 * Typical usage:
 * - Aliased to AssetResolverInterface in `services_test.yaml`.
 */
final class NullAssetResolver implements AssetResolverInterface
{
    /**
     * {@inheritdoc}
     *
     * In test environment, no JavaScript asset is resolved.
     */
    public function resolveJs(string $entry): ?string
    {
        return null;
    }

    /**
     * {@inheritdoc}
     *
     * In test environment, no CSS asset is resolved.
     */
    public function resolveCss(string $entry): ?string
    {
        return null;
    }

    /**
     * {@inheritdoc}
     *
     * In test environment, no module imports are resolved.
     */
    public function resolveImports(string $entry): array
    {
        return [];
    }
}
