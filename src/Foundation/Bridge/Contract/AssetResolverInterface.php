<?php

namespace App\Foundation\Bridge\Contract;

interface AssetResolverInterface
{
    /**
     * @return array<string, mixed>
     */
    public function resolve(string $entry): array;
}