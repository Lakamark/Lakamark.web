<?php

namespace App\Domain\Auth\Event;

use App\Domain\Auth\DTO\IssuedTokenRequestDTO;
use App\Domain\Auth\Entity\User;

readonly class ConfirmationTokenIssuedEvent
{
    public function __construct(
        private User $user,
        private IssuedTokenRequestDTO $issuedTokenRequest,
    ) {
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getIssuedTokenRequest(): IssuedTokenRequestDTO
    {
        return $this->issuedTokenRequest;
    }
}
