<?php

namespace App\Domain\Application\Contract;

use App\Domain\Application\Enum\AccessLevelEnum;
use App\Domain\Application\Enum\ContentStatusEnum;
use App\Domain\Auth\Entity\User;

interface ReadableContentInterface
{
    /**
     * If the visibility content.
     */
    public function getAccessLevel(): AccessLevelEnum;

    /**
     * If the content is draft, private or published.
     */
    public function getStatus(): ContentStatusEnum;

    /**
     * Get the owner content.
     */
    public function getAuthor(): User;
}
