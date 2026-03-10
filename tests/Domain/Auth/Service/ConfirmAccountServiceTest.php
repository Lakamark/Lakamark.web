<?php

namespace App\Tests\Domain\Auth\Service;

use App\Domain\Auth\Entity\TokenRequest;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Enum\TokenRequestType;
use App\Domain\Auth\Enum\UserRole;
use App\Domain\Auth\Service\ConfirmAccountService;
use App\Domain\Auth\Service\TokenRequestService;
use App\Domain\Auth\Service\UserRoleManagerService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\ClockInterface;

class ConfirmAccountServiceTest extends TestCase
{
    public function testConfirmMarksEmailAsConfirmedAndAddsVerifiedRole(): void
    {
        $now = new \DateTimeImmutable('2026-03-10 10:00:00');

        $user = $this->createMock(User::class);
        $user
            ->expects($this->once())
            ->method('confirmEmail')
            ->with($now)
            ->willReturnSelf();

        $tokenRequest = $this->createMock(TokenRequest::class);
        $tokenRequest
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $tokenRequestService = $this->createMock(TokenRequestService::class);
        $tokenRequestService
            ->expects($this->once())
            ->method('consume')
            ->with(
                rawToken: 'plain-token-123',
                type: TokenRequestType::REGISTER_CONFIRMATION,
            )
            ->willReturn($tokenRequest);

        $roleManager = $this->createMock(UserRoleManagerService::class);
        $roleManager
            ->expects($this->once())
            ->method('grant')
            ->with($user, UserRole::USER_VERIFIED);

        $em = $this->createMock(EntityManagerInterface::class);
        $em
            ->expects($this->once())
            ->method('flush');

        $clock = $this->createMock(ClockInterface::class);
        $clock
            ->expects($this->once())
            ->method('now')
            ->willReturn($now);

        $service = new ConfirmAccountService(
            $tokenRequestService,
            $roleManager,
            $em,
            $clock,
        );

        $result = $service->confirm('plain-token-123');

        $this->assertSame($user, $result->user);
        $this->assertTrue($result->emailConfirmed);
        $this->assertTrue($result->roleVerifiedAdded);
    }
}
