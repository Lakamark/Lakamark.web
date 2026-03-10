<?php

namespace App\Domain\Auth\Event;

use App\Domain\Auth\Contract\ConfirmationTokenEventInterface;
use App\Domain\Auth\DTO\IssuedTokenRequestDTO;

readonly class UserRegisteredEvent implements ConfirmationTokenEventInterface
{
    public function __construct(
        private IssuedTokenRequestDTO $issuedTokenRequestDTO,
    ) {
    }

    public function getIssuedTokenRequestDto(): IssuedTokenRequestDTO
    {
        return $this->issuedTokenRequestDTO;
    }
}
