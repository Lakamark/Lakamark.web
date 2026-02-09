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
    public function read(): mixed
    {
        return $this->cache->get($this->cacheKey, function () {
            if (!is_file($this->manifestPath)) {
                return [];
            }

            $json = file_get_contents($this->manifestPath);
            if (false === $json || '' === $json) {
                return [];
            }

            return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        });
    }
}
