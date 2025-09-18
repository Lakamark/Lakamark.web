<?php

namespace App\Foundation\Bridge;

use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * To make a bridge between Symfony and Vite js.
 * You can use a Bridge class to use in a Twig extension.
 */
/**
 * To make a bridge between Symfony and Vite js.
 * You can use a Bridge class to use in a Twig extension.
 */
class AssetBridge
{
    private ?array $paths = null;
    private bool $isProduction;
    private bool $polyfillLoaded = false;

    public function __construct(
        string $env,
        private readonly string $assetPath,
        private readonly string $builderName,
        private readonly string $cacheName,
        private readonly CacheInterface $cache,
        private readonly RequestStack $requestStack,
    ) {
        $this->isProduction = 'prod' === $env;
    }

    /**
     * Render link css.
     *
     * @throws InvalidArgumentException
     */
    public function renderLinkTag(string $name, array $attrs = []): string
    {
        $uri = $this->uri($name.'.css');
        if (strpos($uri, ':3000')) {
            return '';
        }

        $attributes = implode(' ', array_map(fn ($key) => "$key=\"$attrs[$key]\"", array_keys($attrs)));

        return sprintf(
            '<link rel="stylesheet" href="%s" %s>',
            $this->uri($name.'.css'),
            empty($attrs) ? '' : (' '.$attributes)
        );
    }

    /**
     * To generate script tag.
     *
     * @throws InvalidArgumentException
     */
    public function renderJsTags(string $name): string
    {
        $script = $this->preloadAssets($name.'.js').'<script src="'.$this->uri($name.'.js').'" type="module" defer></script>';
        $request = $this->requestStack->getCurrentRequest();

        if (false === $this->polyfillLoaded && $request instanceof Request) {
            $userAgent = $request->headers->get('User-Agent') ?: '';
            if (
                strpos($userAgent, 'Safari')
                && !strpos($userAgent, 'Chrome')
            ) {
                $this->polyfillLoaded = true;
                $script = <<<HTML
                    <script src="//unpkg.com/document-register-element" defer></script>
                    $script
                HTML;
            }
        }

        return $script;
    }

    /**
     * To preload asset to optimize loading.
     *
     * @throws InvalidArgumentException
     */
    private function preloadAssets(string $assetName): string
    {
        if ($this->isProduction) {
            return '';
        }

        $imports = $this->getAssetPaths()[$assetName]['imports'] ?? [];
        $preloads = [];

        foreach ($imports as $import) {
            $preloads[] = <<<HTML
              <link rel="modulepreload" href="{$this->uri($import)}">
            HTML;
        }

        return implode("\n", $preloads);
    }

    /**
     * Get the uri web dev.
     *
     * @throws InvalidArgumentException
     */
    private function uri(string $assetName): string
    {
        if (!$this->isProduction) {
            $request = $this->requestStack->getCurrentRequest();

            return $request ? "{$request->getScheme()}://{$request->getHost()}:3000/assets/$assetName" : '';
        }

        if (strpos($assetName, '.css')) {
            $name = $this->getAssetPaths()[str_replace('.css', '.js', $assetName)]['css'][0] ?? '';
        } else {
            $name = $this->getAssetPaths()[$assetName]['file'] ?? $this->getAssetPaths()[$assetName] ?? '';
        }

        return "/assets/$name";
    }

    /**
     * Retrieve an assets path from a JSON manifest and to write in a cached system.
     *
     * @return string[]
     *
     * @throws InvalidArgumentException
     */
    private function getAssetPaths(): array
    {
        if (null === $this->paths) {
            $this->paths = $this->cache->get($this->cacheName, function () {
                $manifest = $this->getManifestUrl();
                if (file_exists($manifest)) {
                    return json_decode((string) file_get_contents($manifest), true, 512, JSON_THROW_ON_ERROR);
                } else {
                    return [];
                }
            });
        }

        return $this->paths;
    }

    /**
     * Get the manifest url to depend on the asset builder name.
     */
    private function getManifestUrl(): string
    {
        if ('vite' === $this->builderName) {
            return $this->assetPath.'/.vite/manifest.json';
        }

        return $this->assetPath;
    }
}
