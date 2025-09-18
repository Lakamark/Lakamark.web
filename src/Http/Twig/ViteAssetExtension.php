<?php

namespace App\Http\Twig;

use App\Foundation\Bridge\AssetBridge;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Cache\CacheInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * To manage asset with Vite via a Twig extension.
 */
class ViteAssetExtension extends AbstractExtension
{
    private AssetBridge $assetBridge;

    public function __construct(
        string $env,
        private readonly string $assetPath,
        private readonly CacheInterface $cache,
        private readonly RequestStack $requestStack,
    ) {
        $this->assetBridge = new AssetBridge(
            $env,
            $this->assetPath,
            'vite',
            'assets_cached',
            $this->cache,
            $this->requestStack
        );
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('vite_link', $this->link(...), ['is_safe' => ['html']]),
            new TwigFunction('vite_script', $this->script(...), ['is_safe' => ['html']]),
        ];
    }

    /**
     * Create a Link tags (In production).
     *
     * In dev, you create a style tag.
     * The CSS is read in the app.js
     *
     * @throws InvalidArgumentException
     */
    public function link(string $name): string
    {
        return $this->assetBridge->renderLinkTag($name);
    }

    /**
     * Generate script tag.
     *
     * @throws InvalidArgumentException
     */
    public function script(string $name): string
    {
        return $this->assetBridge->renderJsTags($name);
    }
}
