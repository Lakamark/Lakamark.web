<?php

namespace App\Tests\Domain\Application;

use App\Domain\Application\Contract\ReadableContentInterface;
use App\Domain\Application\Enum\AccessLevelEnum;
use App\Domain\Application\Enum\ContentStatusEnum;
use App\Domain\Auth\Entity\User;

/**
 * Test helper used to create Content instances
 * in specific access scenarios (public, private, premium).
 *
 * This class is ONLY used in tests and must not be
 * registered as a Symfony service.
 */
final readonly class ContentFixture implements ReadableContentInterface
{
    public function __construct(
        private AccessLevelEnum $accessLevel,
        private ContentStatusEnum $status,
        private User $author,
    ) {
    }

    public function getAccessLevel(): AccessLevelEnum
    {
        return $this->accessLevel;
    }

    public function getStatus(): ContentStatusEnum
    {
        return $this->status;
    }

    public function getAuthor(): User
    {
        return $this->author;
    }
}
