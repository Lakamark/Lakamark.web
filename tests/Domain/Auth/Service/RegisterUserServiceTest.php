<?php

namespace App\Tests\Domain\Auth\Service;

use App\Domain\Auth\Entity\TokenRequest;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Enum\TokenRequestType;
use App\Domain\Auth\Service\RegisterUserService;
use App\Domain\Auth\Service\TokenRequestService;
use App\Tests\DomainServiceTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Random\RandomException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegisterUserServiceTest extends DomainServiceTestCase
{
    private RegisterUserService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $passwordHasher = $this->service(UserPasswordHasherInterface::class);
        $em = $this->service(EntityManagerInterface::class);
        $tokenRequestService = $this->service(TokenRequestService::class);
        $dispatcher = $this->service(EventDispatcherInterface::class);

        $this->assertInstanceOf(UserPasswordHasherInterface::class, $passwordHasher);
        $this->assertInstanceOf(EntityManagerInterface::class, $em);
        $this->assertInstanceOf(TokenRequestService::class, $tokenRequestService);
        $this->assertInstanceOf(EventDispatcherInterface::class, $dispatcher);

        $this->service = new RegisterUserService(
            $passwordHasher,
            $em,
            $tokenRequestService,
            $dispatcher,
        );
    }

    /**
     * @throws RandomException
     */
    public function testRegisterDoesNotIssueConfirmationTokenForOauthRequest(): void
    {
        $this->setFixedClock(new \DateTimeImmutable('2026-03-09 10:00:00'));

        $user = (new User())
            ->setEmail('oauth@example.com')
            ->setUsername('OAuthUser');

        $request = new Request();

        $result = $this->service->register(
            user: $user,
            plainPassword: 'secret123',
            request: $request,
            isOauthRequest: true,
        );

        $this->flushAndClear();

        $savedUser = $this->repository(User::class)->findOneBy([
            'email' => 'oauth@example.com',
        ]);

        $this->assertInstanceOf(User::class, $savedUser);
        $this->assertNotSame('secret123', $savedUser->getPassword());
        $this->assertNotNull($savedUser->getPassword());
        $this->assertNotNull($savedUser->getCreatedAt());

        $tokenRequest = $this->repository(TokenRequest::class)->findOneBy([
            'user' => $savedUser,
            'type' => TokenRequestType::REGISTER_CONFIRMATION,
        ]);

        $this->assertNull($tokenRequest);
        $this->assertFalse($result->hasIssuedTokenRequest());
        $this->assertTrue($result->isOauthRequest);
        $this->assertSame('oauth@example.com', $result->user->getEmail());
    }
}
