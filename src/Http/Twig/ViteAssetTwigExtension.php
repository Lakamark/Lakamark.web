<?php

namespace App\Http\Twig;

use App\Foundation\Bridge\Contract\AssetBridgeInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Twig extension used to render Vite asset tags from resolved manifest entries.
 *
 * This extension is a presentation-layer adapter between Twig templates and the
 * asset resolution pipeline. It delegates asset lookup to AssetBridge and only
 * converts resolved file paths into HTML tags.
 *
 * Responsibilities:
 * - expose Twig functions for JavaScript and CSS entry files
 * - delegate file lookup to AssetBridge
 * - generate HTML tags for use in templates
 *
 * Non-responsibilities:
 * - reading the Vite manifest
 * - resolving manifest entries directly
 * - handling environment-specific asset logic
 * - swallowing resolution exceptions
 *
 * Pipeline:
 * Twig -> ViteAssetTwigExtension -> AssetBridge -> AssetResolver -> ManifestReader
 */
final class ViteAssetTwigExtension extends AbstractExtension
{
    public function __construct(
        private readonly AssetBridgeInterface $assetBridge,
        private readonly string $publicBasePath = '/assets',
    ) {
    }

    /**
     * Registers the Twig functions exposed by this extension.
     *
     * - vite_entry_script_tags(string $entry): string
     * - vite_entry_link_tags(string $entry): string
     *
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'vite_entry_script_tags',
                $this->renderScriptTags(...),
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'vite_entry_link_tags',
                $this->renderLinkTags(...),
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'vite_entry_modulepreload_tags',
                $this->renderModulePreloadTags(...),
                ['is_safe' => ['html']]
            ),
        ];
    }

    /*
     * Renders HTML <script type="module"> tags for the given entry.
     *
     * The JavaScript files are resolved through AssetBridge. If the entry exists
     * but contains no JavaScript files, an empty string is returned.
     *
     * @param string $entry The manifest entry name
     *
     * @return string
     */
    public function renderScriptTags(string $entry): string
    {
        $files = $this->assetBridge->getJavascriptFiles($entry);

        $tags = array_map(
            fn (string $file): string => sprintf(
                '<script type="module" src="%s" defer></script>',
                $this->buildPublicPath($file)
            ),
            $files
        );

        return implode("\n", $tags);
    }

    /**
     * Renders HTML <link rel="stylesheet"> tags for the given entry.
     *
     * The CSS files are resolved through AssetBridge. If the entry exists
     * but contains no CSS files, an empty string is returned.
     *
     * @param string $entry The manifest entry name
     */
    public function renderLinkTags(string $entry): string
    {
        $files = $this->assetBridge->getCssFiles($entry);

        $tags = array_map(
            fn (string $file): string => sprintf(
                '<link rel="stylesheet" href="%s">',
                $this->buildPublicPath($file)
            ),
            $files
        );

        return implode("\n", $tags);
    }

    /**
     * Renders HTML <link rel="modulepreload"> tags for the given entry imports.
     *
     * The preload files are resolved through AssetBridge from the "imports" field
     * of the manifest entry. If the entry has no preloadable imports, an empty
     * string is returned.
     *
     * @param string $entry The manifest entry name
     */
    public function renderModulePreloadTags(string $entry): string
    {
        $files = $this->assetBridge->getModulePreloadFiles($entry);

        $tags = array_map(
            fn (string $file): string => sprintf(
                '<link rel="modulepreload" href="%s">',
                $this->buildPublicPath($file)
            ),
            $files
        );

        return implode("\n", $tags);
    }

    /**
     * Converts a resolved public asset file into a web path.
     *
     * Example:
     * - assets/app.123.js -> /assets/app.123.js
     */
    private function buildPublicPath(string $file): string
    {
        if (
            str_starts_with($file, 'http://')
            || str_starts_with($file, 'https://')
        ) {
            return $file;
        }

        $basePath = '/'.trim($this->publicBasePath, '/');
        $filePath = ltrim($file, '/');

        return $basePath.'/'.$filePath;
    }
}
