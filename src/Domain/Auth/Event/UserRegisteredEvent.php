<?php

namespace App\Domain\Auth\Event;

use App\Domain\Auth\DTO\IssuedTokenRequestDTO;

readonly class UserRegisteredEvent
{
    public function __construct(
        private IssuedTokenRequestDTO $tokenRequestDTO,
        private bool $useOauthRequest = false,
    ) {
    }

    public function isUseOauthRequest(): bool
    {
        return $this->useOauthRequest;
    }

    public function getIssuedTokenRequestDto(): IssuedTokenRequestDTO
    {
        return $this->tokenRequestDTO;
    }
}
