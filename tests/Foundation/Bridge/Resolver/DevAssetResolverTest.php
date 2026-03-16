<?php

namespace App\Tests\Foundation\Bridge\Resolver;

use App\Foundation\Bridge\Contract\EntryMapperInterface;
use App\Foundation\Bridge\Resolver\DevAssetResolver;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DevAssetResolverTest extends TestCase
{
    #[DataProvider('provideJsEntries')]
    public function testResolveJsInDev(string $entry, string $mappedEntry, string $expectedUrl): void
    {
        
        $mapper = $this->createMock(EntryMapperInterface::class);

        $mapper
            ->expects($this->once())
            ->method('map')
            ->with($entry)
            ->willReturn($mappedEntry);

        $resolver = new DevAssetResolver(
            'https://localhost:3000',
            $mapper
        );

        $this->assertSame($expectedUrl, $resolver->resolveJs($entry));
    }

    public static function provideJsEntries(): iterable
    {
        yield 'logical app entry' => [
            'app',
            'app.ts',
            'https://localhost:3000/assets/app.ts',
        ];

        yield 'logical dashboard entry' => [
            'dashboard',
            'dashboard.ts',
            'https://localhost:3000/assets/dashboard.ts',
        ];

        yield 'explicit ts entry' => [
            'feature.ts',
            'feature.ts',
            'https://localhost:3000/assets/feature.ts',
        ];

        yield 'explicit js entry' => [
            'legacy.js',
            'legacy.js',
            'https://localhost:3000/assets/legacy.js',
        ];

        yield 'nested explicit ts entry' => [
            'admin/panel.ts',
            'admin/panel.ts',
            'https://localhost:3000/assets/admin/panel.ts',
        ];

        yield 'nested explicit js entry' => [
            'legacy/widgets.js',
            'legacy/widgets.js',
            'https://localhost:3000/assets/legacy/widgets.js',
        ];
    }

    public function testResolveCssReturnsNullInDev(): void
    {
        $mapper = $this->createStub(EntryMapperInterface::class);

        $resolver = new DevAssetResolver(
            'https://localhost:3000',
            $mapper
        );

        $this->assertNull($resolver->resolveCss('app'));
    }

    public function testResolveImportsReturnsEmptyArrayInDev(): void
    {
        $mapper = $this->createStub(EntryMapperInterface::class);

        $resolver = new DevAssetResolver(
            'https://localhost:3000',
            $mapper
        );

        $this->assertSame([], $resolver->resolveImports('app'));
    }
}
