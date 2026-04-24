<?php

namespace App\Foundation\Bridge\Contract;

interface AssetBridgeInterface
{
    /**
     * Returns the resolved manifest entry for the given asset entry name.
     *
     * @param string $entry The manifest entry name (e.g. "app.ts")
     *
     * @return array<string, mixed>
     */
    public function getEntry(string $entry): array;

    /**
     * Returns the JavaScript files associated with the given manifest entry.
     *
     * If the entry exists but has no JavaScript file, an empty array is returned.
     *
     * @param string $entry The manifest entry name
     *
     * @return string[]
     */
    public function getJavascriptFiles(string $entry): array;

    /**
     * Returns the CSS files associated with the given manifest entry.
     *
     * If the entry exists but has no CSS files, an empty array is returned.
     *
     * @param string $entry The manifest entry name
     *
     * @return string[]
     */
    public function getCssFiles(string $entry): array;

    /**
     * Returns the JavaScript files that should be preloaded for the given entry.
     *
     * This method inspects the "imports" field of the resolved Vite manifest entry
     * and resolves each imported chunk to its corresponding JavaScript file.
     *
     * Typical usage:
     * - generating <link rel="modulepreload"> tags in Twig
     *
     * Behavior:
     * - returns an empty array when the entry has no imports
     * - ignores invalid or empty import values
     * - removes duplicate files
     * - propagates exceptions if an import references a missing manifest entry
     *
     * @param string $entry The manifest entry name
     *
     * @return string[]
     */
    public function getModulePreloadFiles(string $entry): array;
}
