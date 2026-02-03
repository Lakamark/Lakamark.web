<?php

namespace App\Tests\Domain\Auth\Service;

use App\Domain\Auth\Entity\LoginAttempt;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Repository\LoginAttemptRepository;
use App\Domain\Auth\Service\LoginAttemptsService;
use App\Tests\Helper\FixedClock;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LoginAttemptsServiceTest extends TestCase
{
    public function testItShouldCreateAttempt(): void
    {
        /** @var MockObject|EntityManagerInterface $em */
        $em = $this->getMockBuilder(EntityManagerInterface::class)
            ->getMock();

        /** @var MockObject|LoginAttemptRepository $repository */
        $repository = $this->getStubBuilder(LoginAttemptRepository::class)
            ->disableOriginalConstructor()
            ->getStub();

        $clock = new FixedClock(new \DateTimeImmutable());

        $service = new LoginAttemptsService($repository, $em, $clock);
        $user = new User();

        $em->expects($this->once())->method('persist')->with(
            $this->callback(fn (LoginAttempt $attempt) => $attempt->getUser() === $user)
        );
        $em->expects($this->once())->method('flush');

        $service->increment($user);
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function testHasReachedAttemptForReturnsTrueWhenCountIsAtLeastMax(): void
    {
        $user = $this->createStub(User::class);
        $now = new \DateTimeImmutable('2026-02-03 09:00:00');

        $repository = $this->createMock(LoginAttemptRepository::class);
        $repository
            ->expects($this->once())
            ->method('countRecentAttemptsFor')
            ->with($user, 30, $now)
            ->willReturn(3);

        $clock = new FixedClock($now);

        $em = $this->createStub(EntityManagerInterface::class);

        $svc = new LoginAttemptsService($repository, $em, $clock);

        $this->assertTrue($svc->hasTooManyAttempts($user));
    }
}
