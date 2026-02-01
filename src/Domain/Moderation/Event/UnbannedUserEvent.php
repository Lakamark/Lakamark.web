<?php

namespace App\Domain\Moderation\Event;

use App\Domain\Auth\Entity\User;

readonly class UnbannedUserEvent
{
    public function __construct(
        private User $user,
        private \DateTimeImmutable $now,
    ) {
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getNow(): \DateTimeImmutable
    {
        return $this->now;
    }
}
