<?php

namespace App\Domain\Auth\DTO;

use App\Domain\Auth\Entity\TokenRequest;
use App\Foundation\Security\GeneratedTokenDTO;

readonly class IssuedTokenRequestDTO
{
    public function __construct(
        public TokenRequest $request,
        public GeneratedTokenDTO $generated,
    ) {
    }

    public function getToken(): string
    {
        return $this->generated->token;
    }

    public function getHash(): string
    {
        return $this->generated->hash;
    }
}
