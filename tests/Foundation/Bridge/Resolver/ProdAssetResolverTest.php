<?php

namespace App\Tests\Foundation\Bridge\Resolver;

use App\Foundation\Bridge\Contract\EntryMapperInterface;
use App\Foundation\Bridge\ManifestReader;
use App\Foundation\Bridge\Resolver\ProdAssetResolver;
use PHPUnit\Framework\TestCase;
use Psr\Cache\InvalidArgumentException;

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

        $resolver = new ProdAssetResolver($reader, $entryMapper, '');

        $this->assertSame('/assets/app-AAA.js', $resolver->resolveJs('app'));
        $this->assertSame('/assets/app-AAA.css', $resolver->resolveCss('app'));
    }
}
