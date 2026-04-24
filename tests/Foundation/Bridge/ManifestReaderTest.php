<?php

namespace App\Tests\Foundation\Bridge;

use App\Foundation\Bridge\Exception\AssetManifestInvalidException;
use App\Foundation\Bridge\Exception\AssetManifestNotFoundException;
use App\Foundation\Bridge\ManifestReader;
use PHPUnit\Framework\TestCase;

final class ManifestReaderTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir().'/manifest-reader-tests-'.uniqid('', true);

        if (!mkdir($concurrentDirectory = $this->tempDir, recursive: true) && !is_dir($concurrentDirectory)) {
            $this->fail(sprintf('Unable to create temp directory "%s".', $this->tempDir));
        }
    }

    protected function tearDown(): void
    {
        if (!is_dir($this->tempDir)) {
            return;
        }

        $files = glob($this->tempDir.'/*');
        if (is_array($files)) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }

        rmdir($this->tempDir);
    }

    public function testReadReturnsParsedManifest(): void
    {
        $path = $this->tempDir.'/manifest.json';

        file_put_contents($path, json_encode([
            'app.ts' => [
                'file' => 'assets/app.123.js',
                'css' => ['assets/app.123.css'],
            ],
        ], JSON_THROW_ON_ERROR));

        $reader = new ManifestReader($path);

        $manifest = $reader->read();

        $this->assertIsArray($manifest);
        $this->assertArrayHasKey('app.ts', $manifest);
        $this->assertSame('assets/app.123.js', $manifest['app.ts']['file']);
    }

    public function testReadThrowsWhenManifestFileDoesNotExist(): void
    {
        $path = $this->tempDir.'/missing-manifest.json';
        $reader = new ManifestReader($path);

        $this->expectException(AssetManifestNotFoundException::class);

        $reader->read();
    }

    public function testReadThrowsWhenManifestContainsInvalidJson(): void
    {
        $path = $this->tempDir.'/manifest.json';
        file_put_contents($path, '{invalid json}');

        $reader = new ManifestReader($path);

        $this->expectException(AssetManifestInvalidException::class);

        $reader->read();
    }

    public function testReadThrowsWhenManifestRootIsNotAnObject(): void
    {
        $path = $this->tempDir.'/manifest.json';
        file_put_contents($path, json_encode(['app.ts', 'other.ts'], JSON_THROW_ON_ERROR));

        $reader = new ManifestReader($path);

        $this->expectException(AssetManifestInvalidException::class);

        $reader->read();
    }
}
