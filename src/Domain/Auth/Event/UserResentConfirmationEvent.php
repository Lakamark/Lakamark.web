<?php

namespace App\Domain\Auth\Event;

use App\Domain\Auth\DTO\IssuedTokenRequestDTO;

readonly class UserResentConfirmationEvent
{
    public function __construct(
        private IssuedTokenRequestDTO $tokenRequestDTO,
    ) {
    }

    public function getTokenRequestDTO(): IssuedTokenRequestDTO
    {
        return $this->tokenRequestDTO;
    }
}
