<?php

namespace App\Foundation\Security\Contract;

interface TokenHasherInterface
{
    public function hash(string $token): string;

    public function equals(string $hash, string $token): bool;
}
