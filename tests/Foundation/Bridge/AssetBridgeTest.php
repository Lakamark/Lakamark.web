<?php

namespace App\Tests\Foundation\Bridge;

use App\Foundation\Bridge\AssetBridge;
use App\Foundation\Bridge\Contract\AssetResolverInterface;
use App\Foundation\Bridge\Contract\UserAgentDeciderInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class AssetBridgeTest extends TestCase
{
    public function testScriptTagRendersPreloadsAndModuleScript(): void
    {
        $resolver = $this->createStub(AssetResolverInterface::class);
        $resolver->method('resolveJs')->willReturn('/assets/app.123.js');
        $resolver->method('resolveImports')->willReturn(['/assets/vendor.456.js']);
        $resolver->method('resolveCss')->willReturn(null);

        $decider = $this->createStub(UserAgentDeciderInterface::class);
        $decider->method('shouldLoadCustomElementsPolyfill')->willReturn(false);

        $requestStack = new RequestStack();
        $requestStack->push(new Request()); // UA vide

        $bridge = new AssetBridge($resolver, $requestStack, $decider);

        $html = $bridge->scriptTag('app');

        $this->assertStringContainsString('rel="modulepreload"', $html);
        $this->assertStringContainsString('href="/assets/vendor.456.js"', $html);
        $this->assertStringContainsString('<script src="/assets/app.123.js" type="module" defer></script>', $html);
    }
}
