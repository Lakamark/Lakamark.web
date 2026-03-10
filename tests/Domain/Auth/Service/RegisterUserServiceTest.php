<?php

namespace App\Tests\Domain\Auth\Service;

use App\Domain\Auth\DTO\IssuedTokenRequestDTO;
use App\Domain\Auth\Entity\TokenRequest;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Enum\TokenRequestType;
use App\Domain\Auth\Service\RegisterUserService;
use App\Domain\Auth\Service\TokenRequestService;
use App\Tests\DomainServiceTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Random\RandomException;
use Symfony\Component\Clock\ClockInterface;
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
        $clock = $this->service(ClockInterface::class);

        $this->assertInstanceOf(UserPasswordHasherInterface::class, $passwordHasher);
        $this->assertInstanceOf(EntityManagerInterface::class, $em);
        $this->assertInstanceOf(TokenRequestService::class, $tokenRequestService);
        $this->assertInstanceOf(EventDispatcherInterface::class, $dispatcher);

        $this->service = new RegisterUserService(
            $passwordHasher,
            $em,
            $tokenRequestService,
            $dispatcher,
            $clock
        );
    }

    /**
     * @throws RandomException
     */
    public function testRegisterIssuesConfirmationTokenForLocalRegistration(): void
    {
        $user = (new User())
            ->setEmail('regular@example.com')
            ->setUsername('RegularUser');

        $request = new Request();

        $result = $this->service->register(
            user: $user,
            plainPassword: 'secret123',
            request: $request,
        );

        $this->assertTrue($result->isLocalRegistration());
        $this->assertTrue($result->hasIssuedTokenRequest());
        $this->assertInstanceOf(User::class, $result->user);

        $this->assertInstanceOf(IssuedTokenRequestDTO::class, $result->issuedTokenRequest);
        $this->assertSame(
            TokenRequestType::REGISTER_CONFIRMATION,
            $result->issuedTokenRequest->getType()
        );
        $this->assertNotEmpty($result->issuedTokenRequest->getToken());
        $this->assertNotEmpty($result->issuedTokenRequest->getHash());

        $this->flushAndClear();

        $savedUser = $this->repository(User::class)->findOneBy([
            'email' => 'regular@example.com',
        ]);

        $this->assertInstanceOf(User::class, $savedUser);
        $this->assertNotSame('secret123', $savedUser->getPassword());
        $this->assertNotNull($savedUser->getCreatedAt());

        $savedTokenRequest = $this->repository(TokenRequest::class)->findOneBy([
            'user' => $savedUser,
            'type' => TokenRequestType::REGISTER_CONFIRMATION,
        ]);

        $this->assertInstanceOf(TokenRequest::class, $savedTokenRequest);
        $this->assertSame(TokenRequestType::REGISTER_CONFIRMATION, $savedTokenRequest->getType());
        $this->assertSame($savedUser->getId(), $savedTokenRequest->getUser()->getId());
    }
}
