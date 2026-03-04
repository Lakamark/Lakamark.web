<?php

namespace App\Domain\Auth\Event;

use App\Domain\Auth\Entity\User;

readonly class UserRegisteredEvent
{
    public function __construct(
        private User $user,
        private bool $useOauthRequest = false,
        private string $token,
    ) {
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function isUseOauthRequest(): bool
    {
        return $this->useOauthRequest;
    }

    public function getToken(): string
    {
        return $this->token;
    }
}
