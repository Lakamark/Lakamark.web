<?php

namespace App\Domain\Application\Event;

use App\Domain\Application\Entity\Content;

readonly class BeforeUpdatedContentEvent
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
     * You can set timestamps in the subscriber (edit updatedAt field)
     * Later we will use VicheUploaderBundle
     * If the
     */
    public function getNow(): \DateTimeImmutable
    {
        return $this->now;
    }
}
