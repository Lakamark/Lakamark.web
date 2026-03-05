<?php

namespace App\Tests\Domain\Auth\Service;

use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Enum\UserRole;
use App\Domain\Auth\Service\UserRoleManagerService;
use PHPUnit\Framework\TestCase;

class UserRoleManagerTest extends TestCase
{
    public function testGrantAddsRoleOnce(): void
    {
        $user = $this->createMock(User::class);

        $user->method('getRoles')->willReturn(['ROLE_USER']);

        $user
            ->expects($this->once())
            ->method('setRoles')
            ->with(['ROLE_USER', 'ROLE_USER_VERIFIED']);

        $svc = new UserRoleManagerService();
        $svc->grant($user, UserRole::USER_VERIFIED);
    }

    public function testGrantIsIdempotentNoDuplicates(): void
    {
        $user = $this->createMock(User::class);

        $user
            ->method('getRoles')
            ->willReturn(['ROLE_USER', 'ROLE_USER_VERIFIED']);

        $user->expects($this->once())
            ->method('setRoles')
            ->with(['ROLE_USER', 'ROLE_USER_VERIFIED']);

        $svc = new UserRoleManagerService();
        $svc->grant($user, UserRole::USER_VERIFIED);
    }

    public function testMarkVerifiedEnsuresBothRoles(): void
    {
        $user = $this->createMock(User::class);
        $user
            ->method('getRoles')
            ->willReturn(['ROLE_USER', 'ROLE_USER_VERIFIED']);

        $user->expects($this->once())
            ->method('setRoles')
            ->with(['ROLE_USER']);

        $svc = new UserRoleManagerService();
        $svc->revoke($user, UserRole::USER_VERIFIED);
    }

    public function testUnverifyKeepsRoleUser(): void
    {
        $user = $this->createMock(User::class);

        $user
            ->method('getRoles')
            ->willReturn(['ROLE_USER', 'ROLE_USER_VERIFIED']);

        $user->expects($this->once())
            ->method('setRoles')
            ->with(['ROLE_USER']);

        $svc = new UserRoleManagerService();
        $svc->unverify($user);
    }
}
