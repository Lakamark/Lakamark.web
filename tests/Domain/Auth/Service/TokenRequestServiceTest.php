<?php

namespace App\Tests\Domain\Auth\Service;

use App\Domain\Auth\Entity\TokenRequest;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Enum\TokenRequestType;
use App\Domain\Auth\Exception\TokenRequest\ConsumedTokenException;
use App\Domain\Auth\Exception\TokenRequest\ExpiredTokenException;
use App\Domain\Auth\Exception\TokenRequest\InvalidTokenException;
use App\Domain\Auth\Exception\TokenRequest\RevokedTokenException;
use App\Domain\Auth\Service\TokenRequestService;
use App\Tests\DomainServiceTestCase;
use App\Tests\FixturesLoaderTrait;
use Random\RandomException;

class TokenRequestServiceTest extends DomainServiceTestCase
{
    use FixturesLoaderTrait;

    /**
     * @throws RandomException
     */
    public function testIssueCreatesTokenRequest(): void
    {
        $this->loadFixtures(['users']);

        $now = new \DateTimeImmutable('2026-03-09 10:00:00');
        $this->setFixedClock($now);

        $user = $this->repository(User::class)->findOneBy([]);
        $this->assertInstanceOf(User::class, $user);

        $service = $this->service(TokenRequestService::class);

        $issued = $service->issue(
            $user,
            TokenRequestType::REGISTER_CONFIRMATION
        );

        $this->assertNotSame('', $issued->getToken());
        $this->assertNotSame('', $issued->getHash());

        $request = $issued->request;

        $this->assertInstanceOf(TokenRequest::class, $request);
        $this->assertSame($user->getId(), $request->getUser()->getId());
        $this->assertSame(TokenRequestType::REGISTER_CONFIRMATION, $request->getType());
        $this->assertNull($request->getConsumedAt());
        $this->assertNull($request->getRevokedAt());
    }

    /**
     * @throws RandomException
     * @throws \DateMalformedStringException
     */
    public function testIssueRevokesPreviousUsableTokensForSameUserAndType(): void
    {
        $this->loadFixtures(['users']);

        $initialTime = new \DateTimeImmutable('2026-03-09 10:00:00');
        $this->setFixedClock($initialTime);

        $user = $this->repository(User::class)->findOneBy([]);
        $this->assertInstanceOf(User::class, $user);

        $service = $this->service(TokenRequestService::class);
        $this->assertInstanceOf(TokenRequestService::class, $service);

        $firstIssued = $service->issue(
            $user,
            TokenRequestType::REGISTER_CONFIRMATION,
        );

        $secondTime = $initialTime->modify('+10 minutes');
        $this->setFixedClock($secondTime);

        $secondIssued = $service->issue(
            $user,
            TokenRequestType::REGISTER_CONFIRMATION,
        );

        $this->em->clear();

        $tokenRequestRepository = $this->repository(TokenRequest::class);

        $firstRequest = $tokenRequestRepository->find($firstIssued->request->getId());
        $secondRequest = $tokenRequestRepository->find($secondIssued->request->getId());

        $this->assertInstanceOf(TokenRequest::class, $firstRequest);
        $this->assertInstanceOf(TokenRequest::class, $secondRequest);

        $this->assertNull($firstRequest->getConsumedAt());
        $this->assertNotNull($firstRequest->getRevokedAt());
        $this->assertSame($secondTime->getTimestamp(), $firstRequest->getRevokedAt()?->getTimestamp());

        $this->assertNull($secondRequest->getConsumedAt());
        $this->assertNull($secondRequest->getRevokedAt());
        $this->assertTrue($secondRequest->isUsable($secondTime));
    }

    /**
     * @throws \DateMalformedStringException
     * @throws RandomException
     */
    public function testIssueDoesNotRevokeTokensOfAnotherType(): void
    {
        $this->loadFixtures(['users']);

        $initialTime = new \DateTimeImmutable('2026-03-09 10:00:00');
        $this->setFixedClock($initialTime);

        $user = $this->repository(User::class)->findOneBy([]);
        $this->assertInstanceOf(User::class, $user);

        $service = $this->service(TokenRequestService::class);
        $confirmationIssued = $service->issue(
            $user,
            TokenRequestType::REGISTER_CONFIRMATION,
        );

        $secondTime = $initialTime->modify('+10 minutes');
        $this->setFixedClock($secondTime);

        $resetIssued = $service->issue(
            $user,
            TokenRequestType::PASSWORD_RESET,
        );

        $this->em->clear();

        $tokenRequestRepository = $this->repository(TokenRequest::class);
        $confirmationRequest = $tokenRequestRepository->find($confirmationIssued->request->getId());
        $resetRequest = $tokenRequestRepository->find($resetIssued->request->getId());

        $this->assertInstanceOf(TokenRequest::class, $confirmationRequest);
        $this->assertInstanceOf(TokenRequest::class, $resetRequest);

        $this->assertNull($confirmationRequest->getConsumedAt());
        $this->assertNull($confirmationRequest->getRevokedAt());
        $this->assertTrue($confirmationRequest->isUsable($secondTime));

        $this->assertNull($resetRequest->getConsumedAt());
        $this->assertNull($resetRequest->getRevokedAt());
        $this->assertTrue($resetRequest->isUsable($secondTime));
    }

    /**
     * @throws RandomException
     * @throws \DateMalformedStringException
     */
    public function testConsumeMarksTokenAsConsumed(): void
    {
        $this->loadFixtures(['users']);
        $issuedAt = new \DateTimeImmutable('2026-03-09 10:00:00');
        $this->setFixedClock($issuedAt);

        $user = $this->repository(User::class)->findOneBy([]);
        $this->assertInstanceOf(User::class, $user);

        $user = $this->repository(User::class)->findOneBy([]);
        $this->assertInstanceOf(User::class, $user);

        $service = $this->service(TokenRequestService::class);
        $this->assertInstanceOf(TokenRequestService::class, $service);

        $issued = $service->issue(
            $user,
            TokenRequestType::REGISTER_CONFIRMATION,
        );

        $consumedAt = $issuedAt->modify('+5 minutes');
        $this->setFixedClock($consumedAt);

        $request = $service->consume(
            $issued->getToken(),
            TokenRequestType::REGISTER_CONFIRMATION,
        );

        $this->assertNotNull($request->getConsumedAt());
        $this->assertSame($consumedAt->getTimestamp(), $request->getConsumedAt()?->getTimestamp());
        $this->assertNull($request->getRevokedAt());
    }

    /**
     * @throws \DateMalformedStringException
     * @throws RandomException
     */
    public function testConsumeThrowsWhenTokenIsExpired(): void
    {
        $this->loadFixtures(['users']);
        $issuedAt = new \DateTimeImmutable('2026-03-09 10:00:00');
        $this->setFixedClock($issuedAt);
        $user = $this->repository(User::class)->findOneBy([]);
        $this->assertInstanceOf(User::class, $user);

        $service = $this->service(TokenRequestService::class);
        $this->assertInstanceOf(TokenRequestService::class, $service);

        $issued = $service->issue(
            $user,
            TokenRequestType::REGISTER_CONFIRMATION,
        );

        $expiredAt = $issuedAt
            ->add(TokenRequestType::REGISTER_CONFIRMATION->ttl())
            ->modify('+1 second');

        $this->setFixedClock($expiredAt);

        $this->expectException(ExpiredTokenException::class);

        $service->consume(
            $issued->getToken(),
            TokenRequestType::REGISTER_CONFIRMATION,
        );
    }

    /**
     * @throws RandomException
     * @throws \DateMalformedStringException
     */
    public function testConsumeThrowsWhenTokenIsRevoked(): void
    {
        $this->loadFixtures(['users']);

        $user = $this->repository(User::class)->findOneBy([]);
        $this->assertInstanceOf(User::class, $user);

        $issuedAt = new \DateTimeImmutable('2026-03-09 10:00:00');
        $this->setFixedClock($issuedAt);

        $service = $this->service(TokenRequestService::class);
        $this->assertInstanceOf(TokenRequestService::class, $service);

        $firstIssued = $service->issue(
            $user,
            TokenRequestType::REGISTER_CONFIRMATION,
        );

        $this->setFixedClock($issuedAt->modify('+10 minutes'));

        $service->issue(
            $user,
            TokenRequestType::REGISTER_CONFIRMATION,
        );

        $this->expectException(RevokedTokenException::class);

        $service->consume(
            $firstIssued->getToken(),
            TokenRequestType::REGISTER_CONFIRMATION,
        );
    }

    /**
     * @throws \DateMalformedStringException
     * @throws RandomException
     */
    public function testConsumeThrowsWhenTokenIsAlreadyConsumed(): void
    {
        $this->loadFixtures(['users']);

        $issuedAt = new \DateTimeImmutable('2026-03-09 10:00:00');
        $this->setFixedClock($issuedAt);

        $user = $this->repository(User::class)->findOneBy([]);
        $this->assertInstanceOf(User::class, $user);

        $service = $this->service(TokenRequestService::class);

        $issued = $service->issue(
            $user,
            TokenRequestType::REGISTER_CONFIRMATION,
        );

        $this->setFixedClock($issuedAt->modify('+5 minutes'));

        $service->consume(
            $issued->getToken(),
            TokenRequestType::REGISTER_CONFIRMATION,
        );

        $this->expectException(ConsumedTokenException::class);

        $service->consume(
            $issued->getToken(),
            TokenRequestType::REGISTER_CONFIRMATION,
        );
    }

    public function testConsumeThrowsWhenTokenDoesNotExist(): void
    {
        $this->setFixedClock(new \DateTimeImmutable('2026-03-09 10:00:00'));

        $service = $this->service(TokenRequestService::class);
        $this->assertInstanceOf(TokenRequestService::class, $service);

        $this->expectException(InvalidTokenException::class);

        $service->consume(
            'totally-invalid-token',
            TokenRequestType::REGISTER_CONFIRMATION,
        );
    }
}
