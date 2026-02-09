<?php

namespace App\Foundation\Bridge;

use App\Foundation\Bridge\Contract\AssetResolverInterface;
use App\Foundation\Bridge\Contract\UserAgentDeciderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class AssetBridge
{
    private bool $polyfillLoaded = false;

    public function __construct(
        private readonly AssetResolverInterface $resolver,
        private readonly RequestStack $requestStack,
        private readonly UserAgentDeciderInterface $uaDecider,
        private readonly string $customElementsPolyfillUrl = 'https://unpkg.com/@ungap/custom-elements@1.3.0/min.js',
    ) {
    }

    public function htmlLinkTag(string $entry, array $attrs = []): string
    {
        $href = $this->resolver->resolveCss($entry);
        if (null === $href) {
            return ''; // We load the CSS from js in dev.
        }

        $attributes = $this->renderAttrs($attrs);

        return sprintf('<link rel="stylesheet" href="%s"%s>', $href, $attributes);
    }

    public function scriptTag(string $entry): string
    {
        $src = $this->resolver->resolveJs($entry);
        if (null === $src) {
            return '';
        }

        $preloads = $this->renderModulePreloads($this->resolver->resolveImports($entry));
        $polyfill = $this->renderPolyfillIfNeeded();

        return $polyfill.$preloads.sprintf(
            '<script src="%s" type="module" defer></script>',
            $src
        );
    }

    /** @param list<string> $imports */
    private function renderModulePreloads(array $imports): string
    {
        if ([] === $imports) {
            return '';
        }

        $out = [];
        foreach ($imports as $href) {
            $out[] = sprintf('<link rel="modulepreload" href="%s">', $href);
        }

        return implode("\n", $out)."\n";
    }

    private function renderPolyfillIfNeeded(): string
    {
        if ($this->polyfillLoaded) {
            return '';
        }

        $request = $this->requestStack->getCurrentRequest();
        if (!$request instanceof Request) {
            return '';
        }

        $ua = $request->headers->get('User-Agent') ?? '';
        if (!$this->uaDecider->shouldLoadCustomElementsPolyfill($ua)) {
            return '';
        }

        $this->polyfillLoaded = true;

        return sprintf(
            '<script src="%s" defer></script>'."\n",
            $this->customElementsPolyfillUrl
        );
    }

    private function renderAttrs(array $attrs): string
    {
        if ([] === $attrs) {
            return '';
        }

        $parts = [];
        foreach ($attrs as $k => $v) {
            $key = htmlspecialchars((string) $k, ENT_QUOTES);
            $val = htmlspecialchars((string) $v, ENT_QUOTES);
            $parts[] = sprintf('%s="%s"', $key, $val);
        }

        return ' '.implode(' ', $parts);
    }
}
