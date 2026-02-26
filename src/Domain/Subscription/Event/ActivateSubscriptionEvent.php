<?php

namespace App\Domain\Subscription\Event;

final readonly class ActivateSubscriptionEvent
{
    public function __construct(
        private int $userId,
        private \DateTimeImmutable $periodEnd,
    ) {
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getPeriodEnd(): \DateTimeImmutable
    {
        return $this->periodEnd;
    }
}
