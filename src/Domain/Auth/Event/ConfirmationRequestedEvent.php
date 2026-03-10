<?php

namespace App\Domain\Auth\Event;

use App\Domain\Auth\Contract\ConfirmationTokenEventInterface;
use App\Domain\Auth\DTO\IssuedTokenRequestDTO;

readonly class ConfirmationRequestedEvent implements ConfirmationTokenEventInterface
{
    public function __construct(
        private IssuedTokenRequestDTO $issuedTokenRequestDto,
    ) {
    }

    public function getIssuedTokenRequestDto(): IssuedTokenRequestDTO
    {
        return $this->issuedTokenRequestDto;
    }
}
