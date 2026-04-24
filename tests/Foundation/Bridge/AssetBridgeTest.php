<?php

namespace App\Tests\Foundation\Bridge;

use App\Foundation\Bridge\AssetBridge;
use App\Foundation\Bridge\Contract\AssetResolverInterface;
use App\Foundation\Bridge\Exception\AssetEntryNotFoundException;
use PHPUnit\Framework\TestCase;

final class AssetBridgeTest extends TestCase
{
    public function testGetEntryDelegatesToResolver(): void
    {
        $resolver = $this->createMock(AssetResolverInterface::class);

        $resolver
            ->expects(self::once())
            ->method('resolve')
            ->with('app.ts')
            ->willReturn([
                'file' => 'assets/app.123.js',
                'css' => ['assets/app.123.css'],
            ]);

        $bridge = new AssetBridge($resolver);

        $entry = $bridge->getEntry('app.ts');

        $this->assertSame('assets/app.123.js', $entry['file']);
        $this->assertSame(['assets/app.123.css'], $entry['css']);
    }

    public function testGetEntryPropagatesResolverException(): void
    {
        $resolver = $this->createMock(AssetResolverInterface::class);

        $resolver
            ->expects(self::once())
            ->method('resolve')
            ->with('missing.ts')
            ->willThrowException(new AssetEntryNotFoundException('missing.ts'));

        $bridge = new AssetBridge($resolver);

        $this->expectException(AssetEntryNotFoundException::class);

        $bridge->getEntry('missing.ts');
    }

    public function testGetJavascriptFilesReturnsMainFile(): void
    {
        $resolver = $this->createMock(AssetResolverInterface::class);

        $resolver
            ->expects(self::once())
            ->method('resolve')
            ->with('app.ts')
            ->willReturn([
                'file' => 'assets/app.123.js',
                'css' => ['assets/app.123.css'],
            ]);

        $bridge = new AssetBridge($resolver);

        $this->assertSame(['assets/app.123.js'], $bridge->getJavascriptFiles('app.ts'));
    }

    public function testGetJavascriptFilesReturnsEmptyArrayWhenFileIsMissing(): void
    {
        $resolver = $this->createMock(AssetResolverInterface::class);

        $resolver
            ->expects(self::once())
            ->method('resolve')
            ->with('app.ts')
            ->willReturn([
                'css' => ['assets/app.123.css'],
            ]);

        $bridge = new AssetBridge($resolver);

        $this->assertSame([], $bridge->getJavascriptFiles('app.ts'));
    }

    public function testGetCssFilesReturnsCssFiles(): void
    {
        $resolver = $this->createMock(AssetResolverInterface::class);

        $resolver
            ->expects(self::once())
            ->method('resolve')
            ->with('app.ts')
            ->willReturn([
                'file' => 'assets/app.123.js',
                'css' => ['assets/app.123.css', 'assets/chunk.456.css'],
            ]);

        $bridge = new AssetBridge($resolver);

        self::assertSame(
            ['assets/app.123.css', 'assets/chunk.456.css'],
            $bridge->getCssFiles('app.ts')
        );
    }

    public function testGetCssFilesReturnsEmptyArrayWhenCssIsMissing(): void
    {
        $resolver = $this->createMock(AssetResolverInterface::class);

        $resolver
            ->expects(self::once())
            ->method('resolve')
            ->with('app.ts')
            ->willReturn([
                'file' => 'assets/app.123.js',
            ]);

        $bridge = new AssetBridge($resolver);

        self::assertSame([], $bridge->getCssFiles('app.ts'));
    }

    public function testGetCssFilesFiltersInvalidValues(): void
    {
        $resolver = $this->createMock(AssetResolverInterface::class);

        $resolver
            ->expects(self::once())
            ->method('resolve')
            ->with('app.ts')
            ->willReturn([
                'css' => ['assets/app.123.css', '', null, 42],
            ]);

        $bridge = new AssetBridge($resolver);

        self::assertSame(['assets/app.123.css'], $bridge->getCssFiles('app.ts'));
    }

    public function testGetJavascriptFilesPropagatesResolverException(): void
    {
        $resolver = $this->createMock(AssetResolverInterface::class);

        $resolver
            ->expects(self::once())
            ->method('resolve')
            ->with('missing.ts')
            ->willThrowException(new AssetEntryNotFoundException('missing.ts'));

        $bridge = new AssetBridge($resolver);

        $this->expectException(AssetEntryNotFoundException::class);

        $bridge->getJavascriptFiles('missing.ts');
    }

    public function testGetCssFilesPropagatesResolverException(): void
    {
        $resolver = $this->createMock(AssetResolverInterface::class);

        $resolver
            ->expects(self::once())
            ->method('resolve')
            ->with('missing.ts')
            ->willThrowException(new AssetEntryNotFoundException('missing.ts'));

        $bridge = new AssetBridge($resolver);

        $this->expectException(AssetEntryNotFoundException::class);

        $bridge->getCssFiles('missing.ts');
    }

    public function testGetModulePreloadFilesReturnsImportedFiles(): void
    {
        $resolver = $this->createStub(AssetResolverInterface::class);

        $resolver
            ->method('resolve')
            ->willReturnMap([
                [
                    'app.ts',
                    [
                        'file' => 'assets/app.123.js',
                        'imports' => ['_vendor.456.js', '_chunk.789.js'],
                    ],
                ],
                [
                    '_vendor.456.js',
                    [
                        'file' => 'assets/vendor.456.js',
                    ],
                ],
                [
                    '_chunk.789.js',
                    [
                        'file' => 'assets/chunk.789.js',
                    ],
                ],
            ]);

        $bridge = new AssetBridge($resolver);

        $this->assertSame(
            ['assets/vendor.456.js', 'assets/chunk.789.js'],
            $bridge->getModulePreloadFiles('app.ts')
        );
    }

    public function testGetModulePreloadFilesReturnsEmptyArrayWhenImportsAreMissing(): void
    {
        $resolver = $this->createMock(AssetResolverInterface::class);

        $resolver
            ->expects(self::once())
            ->method('resolve')
            ->with('app.ts')
            ->willReturn([
                'file' => 'assets/app.123.js',
            ]);

        $bridge = new AssetBridge($resolver);

        $this->assertSame([], $bridge->getModulePreloadFiles('app.ts'));
    }

    public function testGetModulePreloadFilesIgnoresInvalidImports(): void
    {
        $resolver = $this->createStub(AssetResolverInterface::class);

        $resolver
            ->method('resolve')
            ->willReturnMap([
                [
                    'app.ts',
                    [
                        'imports' => ['_vendor.456.js', '', null, 42],
                    ],
                ],
                [
                    '_vendor.456.js',
                    [
                        'file' => 'assets/vendor.456.js',
                    ],
                ],
            ]);

        $bridge = new AssetBridge($resolver);

        $this->assertSame(
            ['assets/vendor.456.js'],
            $bridge->getModulePreloadFiles('app.ts')
        );
    }

    public function testGetModulePreloadFilesRemovesDuplicates(): void
    {
        $resolver = $this->createStub(AssetResolverInterface::class);

        $resolver
            ->method('resolve')
            ->willReturnMap([
                [
                    'app.ts',
                    [
                        'imports' => ['_vendor-a.js', '_vendor-b.js'],
                    ],
                ],
                [
                    '_vendor-a.js',
                    [
                        'file' => 'assets/vendor.456.js',
                    ],
                ],
                [
                    '_vendor-b.js',
                    [
                        'file' => 'assets/vendor.456.js',
                    ],
                ],
            ]);

        $bridge = new AssetBridge($resolver);

        $this->assertSame(
            ['assets/vendor.456.js'],
            $bridge->getModulePreloadFiles('app.ts')
        );
    }

    public function testGetModulePreloadFilesPropagatesResolverException(): void
    {
        $resolver = $this->createStub(AssetResolverInterface::class);

        $resolver
            ->method('resolve')
            ->willReturnCallback(static function (string $entry): array {
                if ('app.ts' === $entry) {
                    return ['imports' => ['_missing.js']];
                }

                throw new AssetEntryNotFoundException($entry);
            });

        $bridge = new AssetBridge($resolver);

        $this->expectException(AssetEntryNotFoundException::class);

        $bridge->getModulePreloadFiles('app.ts');
    }

    public function testGetJavascriptFilesReturnsViteClientBeforeMainFile(): void
    {
        $resolver = $this->createMock(AssetResolverInterface::class);

        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->with('assets/app.ts')
            ->willReturn([
                'vite_client' => 'http://localhost:5173/@vite/client',
                'file' => 'http://localhost:5173/assets/app.ts',
            ]);

        $bridge = new AssetBridge($resolver);

        $this->assertSame(
            [
                'http://localhost:5173/@vite/client',
                'http://localhost:5173/assets/app.ts',
            ],
            $bridge->getJavascriptFiles('assets/app.ts')
        );
    }

    public function testGetCssFilesReturnsCssFilesFromImports(): void
    {
        $resolver = $this->createStub(AssetResolverInterface::class);

        $resolver
            ->method('resolve')
            ->willReturnMap([
                [
                    'app.ts',
                    [
                        'file' => 'app.js',
                        'imports' => ['dashboard.ts'],
                    ],
                ],
                [
                    'dashboard.ts',
                    [
                        'file' => 'dashboard.js',
                        'css' => ['dashboard.css'],
                    ],
                ],
            ]);

        $bridge = new AssetBridge($resolver);

        $this->assertSame(['dashboard.css'], $bridge->getCssFiles('app.ts'));
    }
}
