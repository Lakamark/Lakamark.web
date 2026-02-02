<?php

namespace App\Tests\Domain\Moderation\Service;

use App\Domain\Auth\Entity\User;
use App\Domain\Moderation\Entity\UserBan;
use App\Domain\Moderation\Enum\BanReason;
use App\Domain\Moderation\Event\UnbannedUserEvent;
use App\Domain\Moderation\Exception\CannotUnbanBotUserException;
use App\Domain\Moderation\Repository\UserBanRepository;
use App\Domain\Moderation\Service\ModerationService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ModerationServiceTest extends TestCase
{
    public function testUnbanAnUserFlush(): void
    {
        $user = new User();
        $now = new \DateTimeImmutable('2026-01-31 11:34:00');

        $ban = (new UserBan())
            ->setUser($user)
            ->setBanReason(BanReason::SPAM)
            ->setCreatedAt($now->modify('-1 day'))
            ->setExpiresAt($now->modify('+1 day'))
            ->setEndedAt(null);

        $em = $this->createMock(EntityManagerInterface::class);
        $repository = $this->createStub(UserBanRepository::class);
        $dispatcher = $this->createMock(EventDispatcherInterface::class);

        $repository->method('findActiveBanFor')
            ->with($user, $now)
            ->willReturn($ban);

        $em->expects($this->once())->method('flush');

        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(UnbannedUserEvent::class));

        $service = new ModerationService($em, $dispatcher, $repository);
        $service->unbanUser($user, $now);
        $this->assertSame($now, $ban->getEndedAt());
    }

    public function testCannotUnbanBotBan(): void
    {
        $user = new User();
        $now = new \DateTimeImmutable('2026-01-31 11:34:00');

        $ban = (new UserBan())
            ->setUser($user)
            ->setBanReason(BanReason::BOT)
            ->setCreatedAt($now->modify('-1 day'))
            ->setEndedAt(null);

        $em = $this->createMock(EntityManagerInterface::class);
        $repository = $this->createStub(UserBanRepository::class);
        $dispatcher = $this->createMock(EventDispatcherInterface::class);

        $repository->method('findActiveBanFor')
            ->with($user, $now)
            ->willReturn($ban);

        $em->expects($this->never())->method('flush');
        $dispatcher->expects($this->never())->method('dispatch');

        $service = new ModerationService($em, $dispatcher, $repository);

        $this->expectException(CannotUnbanBotUserException::class);
        $service->unbanUser($user, $now);

        // Ensure the EndedAt didnt changed.
        $this->assertNull($ban->getEndedAt());
    }

    public function testCloseExpiredBansEndsAllExpiredBansAndFlushesOnce(): void
    {
        $now = new \DateTimeImmutable('2026-02-02 12:00:00');

        $ban = $this->createMock(UserBan::class);
        $ban2 = $this->createMock(UserBan::class);

        $ban->expects($this->once())->method('endByExpiration');
        $ban2->expects($this->once())->method('endByExpiration');

        $repo = $this->createMock(UserBanRepository::class);
        $repo->expects($this->once())
            ->method('findExpiredOpenBans')
            ->with($now)
            ->willReturn([$ban, $ban2]);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('flush');

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher
            ->expects($this->exactly(2))
            ->method('dispatch');

        $service = new ModerationService($em, $dispatcher, $repo);

        $count = $service->closeExpiredBans($now);
        $this->assertSame(2, $count);
    }

    public function testCloseExpiredBansDoesNotFlushWhenNothingToClose(): void
    {
        $now = new \DateTimeImmutable('2026-02-02 12:00:00');
        $repo = $this->createMock(UserBanRepository::class);
        $repo->expects(self::once())
            ->method('findExpiredOpenBans')
            ->with($now)
            ->willReturn([]);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::never())->method('flush');

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects(self::never())->method('dispatch');

        $service = new ModerationService($em, $dispatcher, $repo);

        $count = $service->closeExpiredBans($now);

        $this->assertSame(0, $count);
    }
}
