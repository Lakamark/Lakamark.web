<?php

namespace App\Domain\Application\Contract;

use App\Domain\Application\Enum\AccessLevelEnum;
use App\Domain\Application\Enum\ContentStatusEnum;
use App\Domain\Auth\Entity\User;

interface ReadableContentInterface
{
    /**
     * To get acceptability level for a content.
     */
    public function getAccessLevel(): AccessLevelEnum;

    /**
     * Get the current status.
     */
    public function getStatus(): ContentStatusEnum;

    /**
     * Get the owner content.
     */
    public function getAuthor(): User;
}
