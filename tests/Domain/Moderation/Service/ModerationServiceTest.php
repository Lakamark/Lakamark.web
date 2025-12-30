<?php

namespace App\Tests\Domain\Moderation\Service;

use App\Domain\Auth\Entity\User;
use App\Domain\Moderation\Entity\UserBan;
use App\Domain\Moderation\Enum\BanReasonEnum;
use App\Domain\Moderation\Event\BanUserEvent;
use App\Domain\Moderation\Event\UnbanUserEvent;
use App\Domain\Moderation\Repository\UserBanRepository;
use App\Domain\Moderation\Service\ModerationService;
use App\Tests\FixturesLoaderTrait;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ModerationServiceTest extends TestCase
{
    use FixturesLoaderTrait;

    public function testBanAnUserFlush(): void
    {
        $user = new User();
        $em = $this->createMock(EntityManagerInterface::class);
        $repository = $this->createStub(UserBanRepository::class);
        $dispatcher = $this->createMock(EventDispatcherInterface::class);

        $service = new ModerationService($em, $dispatcher, $repository);
        $now = new \DateTimeImmutable();

        $em->expects($this->once())
            ->method('persist')
            ->with($this->callback(function ($entity) use ($user, $now) {
                $this->assertInstanceOf(UserBan::class, $entity);
                $this->assertSame(BanReasonEnum::BOT, $entity->getBanReason());
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
            ->with($this->isInstanceOf(BanUserEvent::class));

        $service->banUser($user, BanReasonEnum::BOT, $now);
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

        $service = new ModerationService($em, $dispatcher, $repository);

        $em->expects($this->never())->method('persist');
        $em->expects($this->never())->method('flush');
        $dispatcher->expects($this->never())->method('dispatch');

        $service->banUser($user, BanReasonEnum::BOT, $now);
    }

    public function testUnbanAnUserFlush(): void
    {
        $user = new User();
        $now = new \DateTimeImmutable();

        // Stimulate a ban will be ended sooner.
        $ban = (new UserBan())
            ->setUser($user)
            ->setExpiresAt($now->modify('+1 day'))
            ->setEndedAt(null)
        ;

        $em = $this->createMock(EntityManagerInterface::class);
        $repository = $this->createStub(UserBanRepository::class);
        $dispatcher = $this->createMock(EventDispatcherInterface::class);

        // Stimulate a banned user we want to unban.
        $repository->method('findActiveBanFor')
            ->with($user, $now)
            ->willReturn($ban);

        $em->expects($this->once())->method('flush');

        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(UnbanUserEvent::class));

        $service = new ModerationService($em, $dispatcher, $repository);

        $service->unbanUser($user, $now);

        $this->assertSame($now, $ban->getEndedAt());
    }
}
