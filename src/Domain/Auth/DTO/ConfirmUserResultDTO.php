<?php

namespace App\Domain\Auth\DTO;

use App\Domain\Auth\Entity\User;

class ConfirmUserResultDTO
{
    public function __construct(
        public User $user,
        public bool $emailConfirmed,
        public bool $roleVerifiedAdded,
    ) {
    }
}
