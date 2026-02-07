<?php

namespace App\Domain\Application\Event;

use App\Domain\Application\Entity\Content;

final readonly class BeforeCreatedContentEvent
{
    public function __construct(
        private Content $content,
        private \DateTimeImmutable $now,
    ) {
    }

    public function getContent(): Content
    {
        return $this->content;
    }

    /*
     * You can set timestamps in the subscriber
     */
    public function getNow(): \DateTimeImmutable
    {
        return $this->now;
    }
}
