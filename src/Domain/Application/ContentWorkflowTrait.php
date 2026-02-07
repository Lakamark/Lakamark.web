<?php

namespace App\Domain\Application;

use App\Domain\Application\Enum\ContentStatus;
use App\Domain\Application\Exception\InvalidContentTransitionException;

trait ContentWorkflowTrait
{
    public function publish(\DateTimeImmutable $now): void
    {
        $this->assertHasSlug();

        // Transition interdict: ARCHIVED -> PUBLISHED
        if ($this->isArchived()) {
            throw InvalidContentTransitionException::cannotPublish($this->getStatus());
        }

        // If the content is already published
        if ($this->isPublished()) {
            if (null === $this->getPublishedAt()) {
                $this->setPublishedAt($now);
            }

            return;
        }

        // DRAFT -> PUBLISHED
        $this->setStatus(ContentStatus::PUBLISHED);
        $this->setPublishedAt($now);
    }

    public function archive(\DateTimeImmutable $now): void
    {
        // If the content is already archived.
        if ($this->isArchived()) {
            if (null === $this->getArchivedAt()) {
                $this->setArchivedAt($now);
            }
        }

        // DRAFT/PUBLISHED -> ARCHIVED
        $this->setStatus(ContentStatus::ARCHIVED);
        $this->setArchivedAt($now);
    }

    /**
     * Check if we can edit a content.
     */
    protected function assertEditable(): void
    {
        if ($this->isArchived()) {
            throw InvalidContentTransitionException::cannotEdit($this->getStatus());
        }
    }
}
