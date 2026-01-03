<?php

namespace App\Domain\Application\Contract;

use App\Domain\Application\Enum\AccessLevelEnum;
use App\Domain\Application\Enum\ContentStatusEnum;
use App\Domain\Auth\Entity\User;

/**
 * Use this class to generate stub.
 * If you don't need some to validate the result return in your tests.
 * e.g. $content = new contentStub(AccessLevelEnum::PREMIUM_MEMBER_ONLY, ContentStatusEnum::PUBLISHED, $author).
 */
readonly class contentStub implements ReadableContentInterface
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
