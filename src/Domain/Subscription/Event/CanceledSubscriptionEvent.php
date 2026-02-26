<?php

namespace App\Domain\Subscription\Event;

final readonly class CanceledSubscriptionEvent
{
    public function __construct(
        private int $userId,
        private \DateTimeImmutable $endedAt,
    ) {
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getPeriodEnd(): \DateTimeImmutable
    {
        return $this->endedAt;
    }
}
