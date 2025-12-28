<?php

namespace App\Tests\Domain\Auth\Service;

use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Event\UserBannedEvent;
use App\Domain\Auth\Service\UserBanService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class UserBanServiceTest extends TestCase
{
    public function testBanLocksUserAndFlushes(): void
    {
        $user = new User();
        $em = $this->createMock(EntityManagerInterface::class);
        $dispatcher = $this->createStub(EventDispatcherInterface::class);
        $dispatcher->method('dispatch')->willReturnArgument(0);

        // Persist and flush.
        $em->expects($this->once())
            ->method('persist')
            ->with($user);
        $em->expects($this->once())
            ->method('flush');

        // We call service
        $service = new UserBanService($em, $dispatcher);

        $service->ban($user);
        $this->assertTrue($user->isLocked());
        $this->assertInstanceOf(\DateTimeImmutable::class, $user->getLockedAt());
    }

    public function testBanIsIdempotentDoesNothingIfAlreadyLocked(): void
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $em = $this->createMock(EntityManagerInterface::class);

        $service = new UserBanService($em, $dispatcher);

        $user = new User();
        $user->setLockedAt(new \DateTimeImmutable());

        // If the user already banned. We avoid to call eventManger.
        // We already set the date lockedAt in the database.
        $em->expects($this->never())->method('persist');
        $em->expects($this->never())->method('flush');
        $dispatcher->expects($this->never())->method('dispatch');

        $service->ban($user);

        $this->assertTrue($user->isLocked());
    }

    public function testBanDispatchesUserBannedEvent(): void
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        /**
         * We create a stub, we don't need to add some exception.
         *  We follow PHPUnit recommendation to avoid this warning message:
         *  No expectations were configured for the mock object for (e.g. EventDispatcherInterface).
         * Consider refactoring your test code to use a test stub instead. The #[AllowMockObjectsWithoutExpectations]
         * attribute can be used to opt out of this check.
         *
         * @see https://github.com/sebastianbergmann/phpunit/issues/6437#issuecomment-3621874441
         */
        $em = $this->createStub(EntityManagerInterface::class);

        $service = new UserBanService($em, $dispatcher);

        $user = new User();

        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function ($event) use ($user) {
                $this->assertInstanceOf(UserBannedEvent::class, $event);
                $this->assertSame($user, $event->getUser());

                return true;
            }));

        $service->ban($user);
    }
}
