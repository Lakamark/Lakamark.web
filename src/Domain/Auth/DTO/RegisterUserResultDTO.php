<?php

namespace App\Domain\Auth\DTO;

use App\Domain\Auth\Entity\User;

readonly class RegisterUserResultDTO
{
    public function __construct(
        public User $user,
        public bool $isOauthRequest,
        public ?IssuedTokenRequestDTO $issuedTokenRequest = null,
    ) {
    }

    public function hasIssuedTokenRequest(): bool
    {
        return null !== $this->issuedTokenRequest;
    }
}
