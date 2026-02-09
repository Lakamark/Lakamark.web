<?php

namespace App\Foundation\Bridge;

use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;

readonly class ManifestReader
{
    public function __construct(
        private CacheInterface $cache,
        private string $cacheKey,
        private string $manifestPath,
    ) {
    }

    /** @return array<string, mixed>
     * @throws InvalidArgumentException
     */
    public function read(): array
    {
        $path = $this->manifestPath;

        // IMPORTANT:
        // The cache key is versioned by filemtime to avoid a production-only bug
        // where an empty Vite manifest would be cached forever.
        $version = is_file($path) ? (string) filemtime($path) : '0';
        $key = $this->cacheKey.'.'.$version;

        return $this->cache->get($key, function () use ($path) {
            if (!is_file($path)) {
                return [];
            }

            $json = file_get_contents($path);
            if (false === $json || '' === $json) {
                return [];
            }

            try {
                /** @var array<string, mixed> $data */
                $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

                return is_array($data) ? $data : [];
            } catch (\JsonException) {
                return [];
            }
        });
    }
}
