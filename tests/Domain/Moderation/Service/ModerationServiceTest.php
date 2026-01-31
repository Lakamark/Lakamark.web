<?php

namespace App\Tests\Domain\Moderation\Service;

use App\Domain\Auth\Entity\User;
use App\Domain\Moderation\Entity\UserBan;
use App\Domain\Moderation\Enum\BanReason;
use App\Domain\Moderation\Event\BannedUserEvent;
use App\Domain\Moderation\Event\UnbannedUserEvent;
use App\Domain\Moderation\Repository\UserBanRepository;
use App\Domain\Moderation\Service\ModerationService;
use App\Tests\FixturesLoaderTrait;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ModerationServiceTest extends TestCase
{
    use FixturesLoaderTrait;

    public function testBanAUser(): void
    {
        $user = new User();
        $em = $this->createMock(EntityManagerInterface::class);
        /** @var UserBanRepository $repository */
        $repository = $this->createStub(UserBanRepository::class);
        $dispatcher = $this->createMock(EventDispatcher::class);

        $service = new ModerationService($repository, $em, $dispatcher);
        $now = new \DateTimeImmutable();

        $em->expects($this->once())
            ->method('persist')
            ->with($this->callback(function ($entity) use ($user, $now) {
                $this->assertInstanceOf(UserBan::class, $entity);
                $this->assertSame(BanReason::BOT, $entity->getBanReason());
                $this->assertSame($now, $entity->getCreatedAt());
                $this->assertSame($user, $entity->getUser());
                $this->assertNull($entity->getExpiresAt());
                $this->assertNull($entity->getEndedAt());

                return true;
            }));

        $em->expects($this->once())
            ->method('flush');
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(BannedUserEvent::class));

        $service->banUser($user, BanReason::BOT, $now);
    }

    public function testAlreadyBanNotEmitEvent(): void
    {
        $user = new User();
        $now = new \DateTimeImmutable();

        $em = $this->createMock(EntityManagerInterface::class);
        $repository = $this->createStub(UserBanRepository::class);
        $dispatcher = $this->createMock(EventDispatcherInterface::class);

        // Simulate an already banned user.
        $repository->method('findActiveBanFor')
            ->with($user, $now)
            ->willReturn(new UserBan());

        $service = new ModerationService($repository, $em, $dispatcher);

        $em->expects($this->never())->method('persist');
        $em->expects($this->never())->method('flush');
        $dispatcher->expects($this->never())->method('dispatch');

        $service->banUser($user, BanReason::BOT, $now);
    }

    public function testUnbanAnUserAndFlush(): void
    {
        $user = new User();
        $now = new \DateTimeImmutable('2026-01-31 12:00:00');
        $createdAt = $now->modify('-1 minute');

        $ban = (new UserBan())
            ->setUser($user)
            ->setCreatedAt($createdAt)
            ->setExpiresAt($now->modify('+1 day'))
            ->setEndedAt(null);

        $em = $this->createMock(EntityManagerInterface::class);
        $repository = $this->createMock(UserBanRepository::class);
        $dispatcher = $this->createMock(\Psr\EventDispatcher\EventDispatcherInterface::class);

        $repository->expects($this->once())
            ->method('findActiveBanFor')
            ->with($user, $now)
            ->willReturn($ban);

        $em->expects($this->once())->method('flush');

        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(UnbannedUserEvent::class));

        $service = new ModerationService($repository, $em, $dispatcher);

        $service->unbanUser($user, $now);

        $this->assertSame($now, $ban->getEndedAt());
    }

    public function testEndManuallyIsIdempotent(): void
    {
        $now = new \DateTimeImmutable('2026-01-31 12:00:00');

        $ban = (new UserBan())
            ->setCreatedAt($now->modify('-1 hour'));

        $ban->endManually($now);
        $ban->endManually($now->modify('+1 minute'));

        $this->assertSame($now, $ban->getEndedAt());
    }
}
