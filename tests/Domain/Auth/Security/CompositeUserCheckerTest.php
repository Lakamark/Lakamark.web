<?php

namespace App\Tests\Domain\Auth\Security;

use App\Domain\Auth\Security\CompositeUserChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class CompositeUserCheckerTest extends TestCase
{
    public function testCheckPreAuthCallsAllCheckersInOrder(): void
    {
        $user = $this->createStub(UserInterface::class);

        $c1 = $this->createMock(UserCheckerInterface::class);
        $c1
            ->expects($this->once())
            ->method('checkPreAuth')
            ->with($user);

        $c2 = $this->createMock(UserCheckerInterface::class);
        $c2
            ->expects($this->once())
            ->method('checkPreAuth')
            ->with($user);

        $composite = new CompositeUserChecker([$c1, $c2]);
        $composite->checkPreAuth($user);
    }

    public function testCheckPostAuthCallsAllCheckersInOrder(): void
    {
        $user = $this->createStub(UserInterface::class);

        $c1 = $this->createMock(UserCheckerInterface::class);
        $c1->expects($this->once())->method('checkPostAuth')->with($user);

        $c2 = $this->createMock(UserCheckerInterface::class);
        $c2->expects($this->once())->method('checkPostAuth')->with($user);

        $composite = new CompositeUserChecker([$c1, $c2]);
        $composite->checkPostAuth($user);
    }

    public function testCheckPreAuthStopsWhenACompositeCheckerThrows(): void
    {
        $user = $this->createStub(UserInterface::class);

        $exception = new \RuntimeException('stop');

        $c1 = $this->createMock(UserCheckerInterface::class);
        $c1->expects($this->once())->method('checkPreAuth')->willThrowException($exception);

        $c2 = $this->createMock(UserCheckerInterface::class);
        $c2->expects($this->never())->method('checkPreAuth');

        $composite = new CompositeUserChecker([$c1, $c2]);

        $this->expectExceptionObject($exception);
        $composite->checkPreAuth($user);
    }
}
