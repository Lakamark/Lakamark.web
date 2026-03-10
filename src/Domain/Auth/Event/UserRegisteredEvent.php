<?php

namespace App\Domain\Auth\Event;

use App\Domain\Auth\Contract\ConfirmationTokenEventInterface;
use App\Domain\Auth\DTO\IssuedTokenRequestDTO;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Enum\OAuthProvider;

readonly class UserRegisteredEvent implements ConfirmationTokenEventInterface
{
    public function __construct(
        private User $user,
        private OAuthProvider $authProvider,
        private IssuedTokenRequestDTO $issuedTokenRequestDTO,
    ) {
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getAuthProvider(): OAuthProvider
    {
        return $this->authProvider;
    }

    public function getIssuedTokenRequestDto(): IssuedTokenRequestDTO
    {
        return $this->issuedTokenRequestDTO;
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
