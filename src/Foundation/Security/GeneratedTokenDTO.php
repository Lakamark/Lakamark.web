<?php

namespace App\Foundation\Security;

final readonly class GeneratedTokenDTO
{
    public function __construct(
        public string $token,
        public string $hash,
    ) {
    }
}
