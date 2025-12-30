<?php

namespace App\Tests\Domain\Auth\Security;

use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Exception\BannedUserException;
use App\Domain\Auth\Security\BannedUserChecker;
use App\Domain\Moderation\Entity\UserBan;
use App\Domain\Moderation\Repository\UserBanRepository;
use PHPUnit\Framework\TestCase;

class BannedUserCheckerTest extends TestCase
{
    public function testNotBannedUserDoesNotThrow(): void
    {
        $user = new User();

        $repository = $this->createMock(UserBanRepository::class);

        $repository->expects($this->once())
            ->method('findActiveBanFor')
            ->with(
                $this->identicalTo($user),
                $this->isInstanceOf(\DateTimeInterface::class)
            )
            ->willReturn(null);

        $checker = new BannedUserChecker($repository);

        $checker->checkPostAuth($user);
    }

    public function testBannedUserThrowsException(): void
    {
        $user = new User();
        $ban = new UserBan();

        $repository = $this->createStub(UserBanRepository::class);
        $repository->method('findActiveBanFor')->willReturn($ban);

        $checker = new BannedUserChecker($repository);
        $this->expectException(BannedUserException::class);
        $checker->checkPostAuth($user);
    }
}
