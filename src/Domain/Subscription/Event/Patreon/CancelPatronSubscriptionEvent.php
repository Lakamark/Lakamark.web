<?php

namespace App\Domain\Subscription\Event\Patreon;

final readonly class CancelPatronSubscriptionEvent
{
    public function __construct(
        private int $userId,
        private int $patreonId,
        private \DateTimeImmutable $periodEnd,
    ) {
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getPatreonId(): int
    {
        return $this->patreonId;
    }

    public function getPeriodEnd(): \DateTimeImmutable
    {
        return $this->periodEnd;
    }
}
