<?php

namespace App\Tests\Domain\Auth\Service;

use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Service\ResendConfirmationEmailService;
use App\Tests\DomainServiceTestCase;
use App\Tests\FixturesLoaderTrait;
use Random\RandomException;

class ResendConfirmationEmailServiceTest extends DomainServiceTestCase
{
    use FixturesLoaderTrait;

    /**
     * @throws RandomException
     */
    public function testResendConfirmationIssuesToken(): void
    {
        $this->loadFixtures(['users']);

        $user = $this->repository(User::class)->findOneBy([]);

        $this->assertInstanceOf(User::class, $user);

        $service = $this->service(ResendConfirmationEmailService::class);

        $result = $service->resend($user->getEmail());

        $this->assertTrue($result->success);
    }

    /**
     * @throws RandomException
     */
    public function testResendFailsIfUserAlreadyConfirmed(): void
    {
        $fixtures = $this->loadFixtures(['users']);
        $user = $fixtures['user_confirmed'];

        $this->assertInstanceOf(User::class, $user);
        $this->assertTrue($user->isEmailConfirmed());

        $service = $this->service(ResendConfirmationEmailService::class);
        $result = $service->resend($user->getEmail());

        $this->assertFalse($result->success);
        $this->assertTrue($result->alreadyConfirmed);
    }

    /**
     * @throws RandomException
     */
    public function testResendFailsIfUserNotFound(): void
    {
        $service = $this->service(ResendConfirmationEmailService::class);

        $result = $service->resend('jack_hyperion@lakamark.com');

        $this->assertFalse($result->success);
        $this->assertTrue($result->userNotFound);
    }
}
