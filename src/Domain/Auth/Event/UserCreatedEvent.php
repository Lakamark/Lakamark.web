<?php

namespace App\Domain\Auth\Event;

use App\Domain\Auth\Entity\User;

readonly class UserCreatedEvent
{
    public function __construct(
        private User $user,
        private bool $connectedWithOauth = false,
    ) {
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function isConnectedWithOauth(): bool
    {
        return $this->connectedWithOauth;
    }
}
