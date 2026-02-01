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

        // Optionnel: on s’assure que endedAt n'a pas bougé
        $this->assertNull($ban->getEndedAt());
    }
}
