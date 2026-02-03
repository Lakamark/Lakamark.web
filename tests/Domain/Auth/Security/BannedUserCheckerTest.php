<?php

namespace App\Tests\Domain\Auth\Security;

use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Exception\BannedUserException;
use App\Domain\Auth\Security\BannedUserChecker;
use App\Domain\Moderation\Service\ModerationService;
use App\Tests\Helper\FixedClock;
use PHPUnit\Framework\TestCase;

class BannedUserCheckerTest extends TestCase
{
    public function testPreAuthThrowsBannedUserExceptionWhenUserIsBanned(): void
    {
        $user = $this->createStub(User::class);

        $moderation = $this->createMock(ModerationService::class);
        $moderation
            ->expects($this->once())
            ->method('isUserBanned')
            ->willReturn(true);

        $clock = new FixedClock(new \DateTimeImmutable('2026-02-03 09:00:00'));

        $checker = new BannedUserChecker($moderation, $clock);

        $this->expectException(BannedUserException::class);
        $checker->checkPreAuth($user);
    }

    public function testPreAuthDoesNothingWhenUserIsNotBanned(): void
    {
        $user = $this->createStub(User::class);
        $clock = new FixedClock(new \DateTimeImmutable('2026-02-03 09:00:00'));

        $moderation = $this->createMock(ModerationService::class);
        $moderation
            ->expects($this->once())
            ->method('isUserBanned')
            ->willReturn(false);

        $checker = new BannedUserChecker($moderation, $clock);
        $checker->checkPreAuth($user);

        $this->addToAssertionCount(1);
    }
}
