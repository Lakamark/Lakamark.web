<?php

namespace App\Domain\Application\Contract;

use App\Domain\Application\Enum\AccessLevel;
use App\Domain\Application\Enum\ContentStatus;
use App\Domain\Auth\Entity\User;

interface ReadableContentInterface
{
    /**
     * To get acceptability level for a content.
     */
    public function getAccessLevel(): AccessLevel;

    /**
     * Get the current status.
     */
    public function getStatus(): ContentStatus;

    /**
     * Get the owner content.
     */
    public function getAuthor(): User;
}
