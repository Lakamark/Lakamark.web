<?php

namespace App\Domain\Auth\DTO;

use App\Domain\Auth\Entity\TokenRequest;
use App\Foundation\Security\GeneratedTokenDTO;

readonly class IssuedTokenRequestDTO
{
    public function __construct(
        public TokenRequest $request,
        public GeneratedTokenDTO $issued,
    ) {
    }
}
