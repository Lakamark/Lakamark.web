<?php

namespace App\Domain\Auth\Event;

use App\Domain\Auth\Contract\ConfirmationTokenEventInterface;
use App\Domain\Auth\DTO\IssuedTokenRequestDTO;

readonly class UserResentConfirmationEvent implements ConfirmationTokenEventInterface
{
    public function __construct(
        private IssuedTokenRequestDTO $tokenRequestDTO,
    ) {
    }

    public function getIssuedTokenRequestDto(): IssuedTokenRequestDTO
    {
        return $this->tokenRequestDTO;
    }
}
