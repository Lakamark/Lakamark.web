<?php

namespace App\Tests\Foundation\Bridge;

use App\Foundation\Bridge\ManifestReader;
use PHPUnit\Framework\TestCase;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class ManifestReaderTest extends TestCase
{
    /**
     * Regression test:
     * - Manifest cached as empty array in prod must not persist after deploy.
     *
     * @throws InvalidArgumentException
     * @throws \JsonException
     */
    public function testReadUsesMtimeVersionToAvoidStaleEmptyCache(): void
    {
        $cache = new ArrayAdapter();
        $tmpDir = sys_get_temp_dir().'/lmk_manifest_test';
        @mkdir($tmpDir);
        $manifestPath = $tmpDir.'/manifest.json';
        @unlink($manifestPath);

        $reader = new ManifestReader($cache, 'vite_manifest', $manifestPath);

        // 1. No file = []
        $first = $reader->read();
        $this->assertSame([], $first);

        // 2. Create the manifest AFTER first read
        file_put_contents($manifestPath, json_encode([
            'app.js' => [
                'file' => 'app-AAA.js',
                'css' => ['app-BBB.css'],
                'isEntry' => true,
            ],
        ], JSON_THROW_ON_ERROR));

        // Ensure mtime changes on fast FS
        clearstatcache(true, $manifestPath);

        $second = $reader->read();

        $this->assertArrayHasKey('app.js', $second);
        $this->assertSame('app-AAA.js', $second['app.js']['file']);
        $this->assertSame('app-BBB.css', $second['app.js']['css'][0]);
    }
}
