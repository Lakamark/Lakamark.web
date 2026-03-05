<?php

namespace App\Tests\Domain\Auth\Security;

use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Enum\UserAccess;
use App\Domain\Auth\Exception\BannedUserException;
use App\Domain\Auth\Security\BannedUserChecker;
use App\Domain\Auth\Security\UserAccessPolicy;
use PHPUnit\Framework\TestCase;

class BannedUserCheckerTest extends TestCase
{
    public function testPreAuthThrowsBannedUserExceptionWhenUserIsBanned(): void
    {
        $user = $this->createStub(User::class);

        $userAccessPolicy = $this->createMock(UserAccessPolicy::class);

        $userAccessPolicy
            ->expects($this->once())
            ->method('has')
            ->with($user, UserAccess::BANNED)
            ->willReturn(true);

        $checker = new BannedUserChecker($userAccessPolicy);

        $this->expectException(BannedUserException::class);
        $checker->checkPreAuth($user);
    }

    public function testPreAuthDoesNothingWhenUserIsNotBanned(): void
    {
        $user = $this->createStub(User::class);

        $userAccessPolicy = $this->createMock(UserAccessPolicy::class);

        $userAccessPolicy
            ->expects($this->once())
            ->method('has')
            ->with($user, UserAccess::BANNED)
            ->willReturn(false);

        $checker = new BannedUserChecker($userAccessPolicy);
        $checker->checkPreAuth($user);

        $this->addToAssertionCount(1);
    }
}
