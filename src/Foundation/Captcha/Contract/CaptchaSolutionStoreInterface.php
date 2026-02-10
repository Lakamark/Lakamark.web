<?php

namespace App\Foundation\Captcha\Contract;

interface CaptchaSolutionStoreInterface
{
    public function put(string $key, mixed $solution, int $ttlSeconds): void;

    public function get(string $key): mixed;

    public function has(string $key): bool;

    public function delete(string $key): void;
}
