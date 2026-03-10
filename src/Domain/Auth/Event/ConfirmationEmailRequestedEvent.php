<?php

namespace App\Domain\Auth\Event;

use App\Domain\Auth\DTO\IssuedTokenRequestDTO;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Enum\ConfirmationEmailReason;

readonly class ConfirmationEmailRequestedEvent
{
    public function __construct(
        private User $user,
        private IssuedTokenRequestDTO $issuedTokenRequest,
        private ConfirmationEmailReason $reason,
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

    public function getReason(): ConfirmationEmailReason
    {
        return $this->reason;
    }

    public function isRegisterReason(): bool
    {
        return ConfirmationEmailReason::REGISTER === $this->reason;
    }

    public function isResendReason(): bool
    {
        return ConfirmationEmailReason::RESEND === $this->reason;
    }
}
