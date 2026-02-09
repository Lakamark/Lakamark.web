<?php

namespace App\Tests\Foundation\Bridge\Resolver;

use App\Foundation\Bridge\Resolver\DevAssetResolver;
use PHPUnit\Framework\TestCase;

class DevAssetResolverTest extends TestCase
{
    public function testResolveJsInDev(): void
    {
        $resolver = new DevAssetResolver('https://localhost:3000');

        $this->assertSame('https://localhost:3000/assets/app.js', $resolver->resolveJs('app'));
        $this->assertNull($resolver->resolveCss('app'));
        $this->assertSame([], $resolver->resolveImports('app'));
    }
}
