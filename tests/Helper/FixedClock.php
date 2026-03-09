<?php

namespace App\Tests\Helper;

use Symfony\Component\Clock\ClockInterface;

final class FixedClock implements ClockInterface
{
    private \DateTimeImmutable $now;

    public function __construct(
        ?\DateTimeImmutable $now = null,
    ) {
        $this->now = $now ?? new \DateTimeImmutable('2000-01-01 00:00:00');
    }

    public function setNow(\DateTimeImmutable $now): void
    {
        $this->now = $now;
    }

    public function sleep(float|int $seconds): void
    {
        // no-op in tests
    }

    public function now(): \DateTimeImmutable
    {
        return $this->now;
    }

    public function withTimeZone(\DateTimeZone|string $timezone): static
    {
        $tz = $timezone instanceof \DateTimeZone
            ? $timezone
            : new \DateTimeZone($timezone);

        $clone = clone $this;
        $clone->now = $this->now->setTimezone($tz);

        return $clone;
    }
}
