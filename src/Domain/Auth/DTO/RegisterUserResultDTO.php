<?php

namespace App\Domain\Auth\DTO;

use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Enum\OAuthProvider;

readonly class RegisterUserResultDTO
{
    public function __construct(
        public User $user,
        public OAuthProvider $authProvider,
        public ?IssuedTokenRequestDTO $issuedTokenRequest = null,
    ) {
    }

    public function hasIssuedTokenRequest(): bool
    {
        return null !== $this->issuedTokenRequest;
    }

    public function isLocalRegistration(): bool
    {
        return OAuthProvider::LOCAL === $this->authProvider;
    }

    public function isOauthRegistration(): bool
    {
        return OAuthProvider::LOCAL !== $this->authProvider;
    }
}
