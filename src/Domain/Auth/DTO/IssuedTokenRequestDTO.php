<?php

namespace App\Domain\Auth\DTO;

use App\Domain\Auth\Entity\TokenRequest;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Enum\TokenRequestType;
use App\Foundation\Security\GeneratedTokenDTO;

readonly class IssuedTokenRequestDTO
{
    public function __construct(
        public TokenRequest $request,
        public GeneratedTokenDTO $generated,
    ) {
    }

    public function getUser(): User
    {
        return $this->request->getUser();
    }

    public function getToken(): string
    {
        return $this->generated->token;
    }

    public function getHash(): string
    {
        return $this->generated->hash;
    }

    public function getType(): TokenRequestType
    {
        return $this->request->getType();
    }
}
