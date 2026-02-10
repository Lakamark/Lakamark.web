<?php

namespace App\Foundation\Captcha\Store;

use App\Foundation\Captcha\Contract\CaptchaSolutionStoreInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class CacheCaptchaSolutionStore implements CaptchaSolutionStoreInterface
{
    private const string PREFIX = 'captcha_solution_';

    public function __construct(
        private readonly CacheInterface $cache,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function put(string $key, mixed $solution, int $ttlSeconds): void
    {
        $this->cache->delete(self::PREFIX.$key);

        $this->cache->get(self::PREFIX.$key, function (ItemInterface $item) use ($solution, $ttlSeconds) {
            $item->expiresAfter($ttlSeconds);

            return $solution;
        });
    }

    /**
     * @throws InvalidArgumentException
     */
    public function get(string $key): mixed
    {
        // Return null is now / expired
        return $this->cache->get(self::PREFIX.$key, static fn () => null);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function has(string $key): bool
    {
        return null !== $this->get($key);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function delete(string $key): void
    {
        $this->cache->delete(self::PREFIX.$key);
    }
}
