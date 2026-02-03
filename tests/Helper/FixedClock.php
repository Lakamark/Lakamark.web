<?php

namespace App\Tests\Helper;

use Symfony\Component\Clock\ClockInterface;

final readonly class FixedClock implements ClockInterface
{
    public function __construct(
        private \DateTimeImmutable $now,
    ) {
    }

    public function sleep(float|int $seconds): void
    {
        // We cant put on pause during process test!
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

        return new self($this->now->setTimezone($tz));
    }
}
