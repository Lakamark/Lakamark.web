<?php

namespace App\Domain\Auth\DTO;

use App\Domain\Auth\Entity\User;

readonly class ResendConfirmationResultDTO
{
    public function __construct(
        public bool $success,
        public ?User $user = null,
        public bool $alreadyConfirmed = false,
        public bool $userNotFound = false,
    ) {
    }
}
