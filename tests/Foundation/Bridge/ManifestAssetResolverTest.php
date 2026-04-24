<?php

namespace App\Tests\Foundation\Bridge;

use App\Foundation\Bridge\Contract\ManifestReaderInterface;
use App\Foundation\Bridge\Exception\AssetEntryNotFoundException;
use App\Foundation\Bridge\Resolver\ManifestAssetResolver;
use PHPUnit\Framework\TestCase;

final class ManifestAssetResolverTest extends TestCase
{
    public function testResolveManifest(): void
    {
        $reader = $this->createStub(ManifestReaderInterface::class);
        $reader->method('read')->willReturn([
            'app.ts' => [
                'file' => 'assets/app.123.js',
                'css' => ['assets/app.123.css'],
            ],
        ]);

        $resolver = new ManifestAssetResolver($reader);

        $entry = $resolver->resolve('app.ts');

        $this->assertSame('assets/app.123.js', $entry['file']);
    }

    public function testResolveThrowsWhenEntryNotFound(): void
    {
        $reader = $this->createStub(ManifestReaderInterface::class);

        $reader->method('read')->willReturn([]);

        $resolver = new ManifestAssetResolver($reader);

        $this->expectException(AssetEntryNotFoundException::class);

        $resolver->resolve('app.ts');
    }
}
