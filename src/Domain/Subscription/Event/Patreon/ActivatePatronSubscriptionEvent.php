<?php

namespace App\Domain\Subscription\Event\Patreon;

final readonly class ActivatePatronSubscriptionEvent
{
    public function __construct(
        private int $userId,
        private string $patreonId,
        private \DateTimeImmutable $periodEnd,
    ) {
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getPatreonId(): string
    {
        return $this->patreonId;
    }

    public function getPeriodEnd(): \DateTimeImmutable
    {
        return $this->periodEnd;
    }
}
