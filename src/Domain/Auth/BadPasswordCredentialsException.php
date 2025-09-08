<?php

namespace App\Domain\Auth;

use App\Domain\Auth\Entity\User;

readonly class BadPasswordCredentialsException
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
