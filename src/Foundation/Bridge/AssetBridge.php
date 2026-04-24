<?php

namespace App\Foundation\Bridge;

use App\Foundation\Bridge\Contract\AssetBridgeInterface;
use App\Foundation\Bridge\Contract\AssetResolverInterface;

/**
 * Application-facing facade for resolved asset entries.
 *
 * AssetBridge is the public entry point used by higher-level layers such as
 * Twig extensions. Its purpose is to expose a simple and stable API while
 * delegating all resolution work to the configured AssetResolver.
 *
 * Responsibilities:
 * - receive asset entry requests from application-facing code
 * - delegate resolution to the resolver layer
 * - expose resolved asset files through a simpler API
 *
 * Non-responsibilities:
 * - reading the manifest file
 * - implementing resolution logic
 * - applying environment-specific behavior
 * - rendering HTML
 * - silently swallowing pipeline exceptions
 *
 * Pipeline:
 * Twig Extension -> AssetBridge -> AssetResolver -> ManifestReader
 */
final readonly class AssetBridge implements AssetBridgeInterface
{
    public function __construct(
        private AssetResolverInterface $resolver,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getEntry(string $entry): array
    {
        return $this->resolver->resolve($entry);
    }

    /**
     * @return string[]
     */
    public function getJavascriptFiles(string $entry): array
    {
        $resolvedEntry = $this->getEntry($entry);

        $files = [];

        // Load the Vite Client (only in dev)
        $viteClient = $resolvedEntry['vite_client'] ?? null;
        if (is_string($viteClient) && '' !== $viteClient) {
            $files[] = $viteClient;
        }

        // Main file (app.js or app.ts)
        $file = $resolvedEntry['file'] ?? null;
        if (is_string($file) && '' !== $file) {
            $files[] = $file;
        }

        return $files;
    }

    /**
     * @return string[]
     */
    /**
     * @return string[]
     */
    public function getCssFiles(string $entry): array
    {
        $resolvedEntry = $this->getEntry($entry);
        
        $files = $this->extractCssFiles($resolvedEntry);
        
        $imports = $resolvedEntry['imports'] ?? [];
        
        if (is_array($imports)) {
            foreach ($imports as $import) {
                if (!is_string($import) || '' === $import) {
                    continue;
                }
                
                $importEntry = $this->getEntry($import);
                
                $files = array_merge(
                    $files,
                    $this->extractCssFiles($importEntry)
                );
            }
        }
        
        return array_values(array_unique($files));
    }

    /**
     * Returns the JavaScript files that should be preloaded for the given entry.
     *
     * This method resolves the current entry, inspects its "imports" field, and
     * resolves each imported manifest entry to its JavaScript file.
     *
     * If the entry has no imports, an empty array is returned.
     * Invalid import values are ignored.
     *
     * @param string $entry The manifest entry name
     *
     * @return string[]
     */
    public function getModulePreloadFiles(string $entry): array
    {
        $resolvedEntry = $this->getEntry($entry);
        $imports = $resolvedEntry['imports'] ?? [];

        if (!is_array($imports)) {
            return [];
        }

        $files = [];

        foreach ($imports as $import) {
            if (!is_string($import) || '' === $import) {
                continue;
            }

            $importEntry = $this->getEntry($import);
            $file = $importEntry['file'] ?? null;

            if (is_string($file) && '' !== $file) {
                $files[] = $file;
            }
        }

        return array_values(array_unique($files));
    }

    /**
     * @param array<string, mixed> $entry
     *
     * @return string[]
     */
    private function extractCssFiles(array $entry): array
    {
        $css = $entry['css'] ?? [];

        if (!is_array($css)) {
            return [];
        }

        return array_values(array_filter(
            $css,
            static fn (mixed $file): bool => is_string($file) && '' !== $file
        ));
    }
}
