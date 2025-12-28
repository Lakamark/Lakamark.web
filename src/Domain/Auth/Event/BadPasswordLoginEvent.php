<?php

namespace App\Domain\Auth\Event;

use App\Domain\Auth\Entity\User;

readonly class BadPasswordLoginEvent
{
    public function __construct(
        private User $user,
    ) {
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
