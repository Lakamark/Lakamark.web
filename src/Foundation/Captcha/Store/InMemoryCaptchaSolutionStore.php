<?php

namespace App\Foundation\Captcha\Store;

use App\Foundation\Captcha\Contract\CaptchaSolutionStoreInterface;

final class InMemoryCaptchaSolutionStore implements CaptchaSolutionStoreInterface
{
    /** @var array<string, mixed> */
    private array $items = [];

    public function put(string $key, mixed $solution, int $ttlSeconds): void
    {
        // TTl disabled for test environments.
        $this->items[$key] = $solution;
    }

    public function get(string $key): mixed
    {
        return $this->items[$key] ?? null;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->items);
    }

    public function delete(string $key): void
    {
        unset($this->items[$key]);
    }
}
