<?php

namespace App\Tests\Foundation\Bridge;

use App\Foundation\Bridge\Contract\AssetResolverInterface;
use App\Foundation\Bridge\Resolver\EnvironmentAssetResolver;
use App\Foundation\Bridge\Resolver\ViteDevAssetResolver;
use App\Tests\KernelTestCase;

final class AssetResolverWiringTest extends KernelTestCase
{
    public function testAssetResolverInterfacePointsToEnvironmentResolver(): void
    {
        self::bootKernel();

        $resolver = static::getContainer()->get(AssetResolverInterface::class);

        $this->assertInstanceOf(EnvironmentAssetResolver::class, $resolver);
    }
    
    public function testViteDevAssetResolverUsesConfiguredDevServerUrl(): void
    {
        self::bootKernel();
        
        $resolver = static::getContainer()->get(ViteDevAssetResolver::class);
        $entry = $resolver->resolve('app.ts');
        
        $this->assertSame('https://localhost:3000/assets/@vite/client', $entry['vite_client']);
        $this->assertSame('https://localhost:3000/assets/app.ts', $entry['file']);
        $this->assertSame([], $entry['css']);
        $this->assertSame([], $entry['imports']);
    }
}
