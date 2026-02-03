<?php

namespace App\Tests\Domain\Auth\Security;

use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Exception\TooManyAttemptsException;
use App\Domain\Auth\Security\LoginAttemptsUserChecker;
use App\Domain\Auth\Service\LoginAttemptsService;
use PHPUnit\Framework\TestCase;

class LoginAttemptsUserCheckerTest extends TestCase
{
    public function testPreAuthThrowsWhenTooManyAttempts(): void
    {
        $user = $this->createStub(User::class);

        $attempts = $this->createMock(LoginAttemptsService::class);
        $attempts
            ->expects($this->once())
            ->method('hasTooManyAttempts')
            ->willReturn(true);

        $checker = new LoginAttemptsUserChecker($attempts);
        $this->expectException(TooManyAttemptsException::class);
        $checker->checkPreAuth($user);
    }

    public function testPreAuthDoesNothingWhenAttemptsAreOk(): void
    {
        $user = $this->createStub(User::class);

        $attempts = $this->createMock(LoginAttemptsService::class);
        $attempts
            ->expects($this->once())
            ->method('hasTooManyAttempts')
            ->willReturn(false);

        $checker = new LoginAttemptsUserChecker($attempts);
        $checker->checkPreAuth($user);

        $this->addToAssertionCount(1);
    }
}
