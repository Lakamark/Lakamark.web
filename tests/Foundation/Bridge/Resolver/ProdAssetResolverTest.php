<?php

namespace App\Tests\Foundation\Bridge\Resolver;

use App\Foundation\Bridge\Contract\EntryMapperInterface;
use App\Foundation\Bridge\ManifestReader;
use App\Foundation\Bridge\Resolver\ProdAssetResolver;
use PHPUnit\Framework\TestCase;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

/**
 * Regression tests for production-only bugs:
 * - Prevent stale Vite manifest cache in prod
 * - Ensure scalar DI arguments are wired before services
 *
 * These tests protect against container optimization issues
 * that only appear in compiled prod containers.
 */
class ProdAssetResolverTest extends TestCase
{
    /**
     * @throws InvalidArgumentException
     */
    public function testResolveCssFromEntryJsCssArray(): void
    {
        $reader = $this->createStub(ManifestReader::class);
        $entryMapper = $this->createStub(EntryMapperInterface::class);
        $entryMapper
            ->method('map')
            ->with('app')
            ->willReturn('assets/app.js');

        $reader->method('read')->willReturn([
            'assets/app.js' => [
                'file' => 'assets/app-AAA.js',
                'css' => ['assets/app-AAA.css'],
            ],
        ]);
        $resolver = new ProdAssetResolver('', $reader, $entryMapper);

        $this->assertSame('/assets/app-AAA.js', $resolver->resolveJs('app'));
        $this->assertSame('/assets/app-AAA.css', $resolver->resolveCss('app'));
    }

    /**
     * @throws \JsonException
     * @throws InvalidArgumentException
     */
    public function testResolveJsAndCssFromViteManifest(): void
    {
        $cache = new ArrayAdapter();

        $tmpDir = sys_get_temp_dir().'/lmk_resolver_test';
        @mkdir($tmpDir);
        $manifestPath = $tmpDir.'/manifest.json';

        file_put_contents($manifestPath, json_encode([
            'app.js' => [
                'file' => 'app-CYPhJufM.js',
                'css' => ['app-BP4JzgtB.css'],
                'isEntry' => true,
            ],
        ], JSON_THROW_ON_ERROR));

        $reader = new ManifestReader($cache, 'vite_manifest', $manifestPath);

        $entryMapper = new class implements EntryMapperInterface {
            public function map(string $entry): string
            {
                $entry = trim($entry);
                if ('' === $entry) {
                    throw new \InvalidArgumentException();
                }

                // prod manifest key format
                return str_contains($entry, '.') ? $entry : $entry.'.js';
            }
        };

        // NOTE: string first (your stabilized constructor)
        $resolver = new ProdAssetResolver('/assets', $reader, $entryMapper);

        $this->assertSame('/assets/app-CYPhJufM.js', $resolver->resolveJs('app'));
        $this->assertSame('/assets/app-BP4JzgtB.css', $resolver->resolveCss('app'));
    }
}
