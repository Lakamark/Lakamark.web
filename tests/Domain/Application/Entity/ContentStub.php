<?php

namespace App\Tests\Domain\Application\Entity;

use App\Domain\Application\Entity\Content;

final class ContentStub extends Content
{
    public function __construct(
        private string $title = '',
        private ?string $slug = null,
    ) {
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }
}
