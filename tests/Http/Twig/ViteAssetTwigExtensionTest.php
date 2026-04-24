<?php

namespace App\Tests\Http\Twig;

use App\Foundation\Bridge\Contract\AssetBridgeInterface;
use App\Foundation\Bridge\Exception\AssetEntryNotFoundException;
use App\Http\Twig\ViteAssetTwigExtension;
use PHPUnit\Framework\TestCase;
use Twig\TwigFunction;

final class ViteAssetTwigExtensionTest extends TestCase
{
    public function testGetFunctionsReturnsExpectedTwigFunctions(): void
    {
        $bridge = $this->createStub(AssetBridgeInterface::class);
        $extension = new ViteAssetTwigExtension($bridge);

        $functions = $extension->getFunctions();

        $this->assertCount(3, $functions);
        $this->assertContainsOnlyInstancesOf(TwigFunction::class, $functions);

        $this->assertSame('vite_entry_script_tags', $functions[0]->getName());
        $this->assertSame('vite_entry_link_tags', $functions[1]->getName());
        $this->assertSame('vite_entry_modulepreload_tags', $functions[2]->getName());
    }

    public function testRenderScriptTagsReturnsModuleScriptTags(): void
    {
        $bridge = $this->createMock(AssetBridgeInterface::class);

        $bridge
            ->expects(self::once())
            ->method('getJavascriptFiles')
            ->with('app.ts')
            ->willReturn([
                'app.123.js',
                'chunk.456.js',
            ]);

        $extension = new ViteAssetTwigExtension($bridge);

        $result = $extension->renderScriptTags('app.ts');

        $this->assertSame(
            '<script type="module" src="/assets/app.123.js" defer></script>'."\n".
            '<script type="module" src="/assets/chunk.456.js" defer></script>',
            $result
        );
    }

    public function testRenderScriptTagsReturnsEmptyStringWhenNoJavascriptFilesExist(): void
    {
        $bridge = $this->createMock(AssetBridgeInterface::class);

        $bridge
            ->expects(self::once())
            ->method('getJavascriptFiles')
            ->with('app.ts')
            ->willReturn([]);

        $extension = new ViteAssetTwigExtension($bridge);

        $this->assertSame('', $extension->renderScriptTags('app.ts'));
    }

    public function testRenderLinkTagsReturnsStylesheetLinks(): void
    {
        $bridge = $this->createMock(AssetBridgeInterface::class);

        $bridge
            ->expects(self::once())
            ->method('getCssFiles')
            ->with('app.ts')
            ->willReturn([
                'app.123.css',
                'chunk.456.css',
            ]);

        $extension = new ViteAssetTwigExtension($bridge);

        $result = $extension->renderLinkTags('app.ts');

        $this->assertSame(
            '<link rel="stylesheet" href="/assets/app.123.css">'."\n".
            '<link rel="stylesheet" href="/assets/chunk.456.css">',
            $result
        );
    }

    public function testRenderLinkTagsReturnsEmptyStringWhenNoCssFilesExist(): void
    {
        $bridge = $this->createMock(AssetBridgeInterface::class);

        $bridge
            ->expects(self::once())
            ->method('getCssFiles')
            ->with('app.ts')
            ->willReturn([]);

        $extension = new ViteAssetTwigExtension($bridge);

        $this->assertSame('', $extension->renderLinkTags('app.ts'));
    }

    public function testRenderScriptTagsPropagatesBridgeException(): void
    {
        $bridge = $this->createMock(AssetBridgeInterface::class);

        $bridge
            ->expects(self::once())
            ->method('getJavascriptFiles')
            ->with('missing.ts')
            ->willThrowException(new AssetEntryNotFoundException('missing.ts'));

        $extension = new ViteAssetTwigExtension($bridge);

        $this->expectException(AssetEntryNotFoundException::class);

        $extension->renderScriptTags('missing.ts');
    }

    public function testRenderLinkTagsPropagatesBridgeException(): void
    {
        $bridge = $this->createMock(AssetBridgeInterface::class);

        $bridge
            ->expects(self::once())
            ->method('getCssFiles')
            ->with('missing.ts')
            ->willThrowException(new AssetEntryNotFoundException('missing.ts'));

        $extension = new ViteAssetTwigExtension($bridge);

        $this->expectException(AssetEntryNotFoundException::class);

        $extension->renderLinkTags('missing.ts');
    }

    public function testRenderModulePreloadTagsReturnsModulePreloadLinks(): void
    {
        $bridge = $this->createMock(AssetBridgeInterface::class);

        $bridge
            ->expects($this->once())
            ->method('getModulePreloadFiles')
            ->with('app.ts')
            ->willReturn([
                'vendor.456.js',
                'chunk.789.js',
            ]);

        $extension = new ViteAssetTwigExtension($bridge);

        $result = $extension->renderModulePreloadTags('app.ts');

        $this->assertSame(
            '<link rel="modulepreload" href="/assets/vendor.456.js">'."\n".
            '<link rel="modulepreload" href="/assets/chunk.789.js">',
            $result
        );
    }

    public function testRenderModulePreloadTagsReturnsEmptyStringWhenNoImportsExist(): void
    {
        $bridge = $this->createMock(AssetBridgeInterface::class);

        $bridge
            ->expects($this->once())
            ->method('getModulePreloadFiles')
            ->with('app.ts')
            ->willReturn([]);

        $extension = new ViteAssetTwigExtension($bridge);

        $this->assertSame('', $extension->renderModulePreloadTags('app.ts'));
    }

    public function testRenderModulePreloadTagsPropagatesBridgeException(): void
    {
        $bridge = $this->createMock(AssetBridgeInterface::class);

        $bridge
            ->expects(self::once())
            ->method('getModulePreloadFiles')
            ->with('missing.ts')
            ->willThrowException(new AssetEntryNotFoundException('missing.ts'));

        $extension = new ViteAssetTwigExtension($bridge);

        $this->expectException(AssetEntryNotFoundException::class);

        $extension->renderModulePreloadTags('missing.ts');
    }

    public function testRenderScriptTagsPrefixesPublicBasePath(): void
    {
        $bridge = $this->createMock(AssetBridgeInterface::class);

        $bridge
            ->expects($this->once())
            ->method('getJavascriptFiles')
            ->with('app.ts')
            ->willReturn([
                'app-123.js',
            ]);

        $extension = new ViteAssetTwigExtension($bridge, '/assets');

        $result = $extension->renderScriptTags('app.ts');

        $this->assertSame(
            '<script type="module" src="/assets/app-123.js" defer></script>',
            $result
        );
    }

    public function testRenderLinkTagsPrefixesPublicBasePath(): void
    {
        $bridge = $this->createMock(AssetBridgeInterface::class);

        $bridge
            ->expects($this->once())
            ->method('getCssFiles')
            ->with('app.ts')
            ->willReturn([
                'app-123.css',
            ]);

        $extension = new ViteAssetTwigExtension($bridge, '/assets');

        $result = $extension->renderLinkTags('app.ts');

        $this->assertSame(
            '<link rel="stylesheet" href="/assets/app-123.css">',
            $result
        );
    }

    public function testRenderScriptTagsDoesNotPrefixAbsoluteUrls(): void
    {
        $bridge = $this->createMock(AssetBridgeInterface::class);

        $bridge
            ->expects($this->once())
            ->method('getJavascriptFiles')
            ->with('app.ts')
            ->willReturn([
                'https://localhost:3000/assets/app.ts',
            ]);

        $extension = new ViteAssetTwigExtension($bridge, '/assets');

        $result = $extension->renderScriptTags('app.ts');

        $this->assertSame(
            '<script type="module" src="https://localhost:3000/assets/app.ts" defer></script>',
            $result
        );
    }
}
