<?php

namespace App\Domain\Auth\Event;

use App\Domain\Auth\Entity\User;

/**
 * Represent the event of a user when the
 * onAuthenticationFailure is dispatched.
 */
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
