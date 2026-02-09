<?php

namespace App\Http\Twig;

use App\Foundation\Bridge\AssetBridge;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class ViteAssetTwigExtension extends AbstractExtension
{
    public function __construct(
        private readonly AssetBridge $assetBridge,
    ) {
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

    public function link(string $entry): string
    {
        return $this->assetBridge->htmlLinkTag($entry);
    }

    public function script(string $entry): string
    {
        return $this->assetBridge->scriptTag($entry);
    }
}
