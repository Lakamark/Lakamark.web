<?php

namespace App\Tests\Domain\Auth\Service;

use App\Domain\Auth\Entity\TokenRequest;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Enum\TokenRequestType;
use App\Domain\Auth\Exception\InvalidTokenException;
use App\Domain\Auth\Repository\TokenRequestRepository;
use App\Domain\Auth\Service\TokenRequestService;
use App\Foundation\Security\GeneratedTokenDTO;
use App\Foundation\Security\TokenHasher;
use App\Foundation\Security\TokenIssuer;
use PHPUnit\Framework\TestCase;
use Random\RandomException;

class TokenRequestServiceTest extends TestCase
{
    /**
     * @throws \DateMalformedStringException
     * @throws RandomException
     */
    public function testIssueCreatesTokenAndReturnsRawToken(): void
    {
        $repository = $this->createMock(TokenRequestRepository::class);
        $tokenHasher = $this->createStub(TokenHasher::class);
        $tokenIssuer = $this->createMock(TokenIssuer::class);

        $generatedToken = new GeneratedTokenDTO(
            token: 'raw_token',
            hash: 'hash-from-hasher'
        );

        $tokenIssuer
            ->expects($this->once())
            ->method('issue')
            ->willReturn($generatedToken);

        $repository
            ->expects($this->once())
            ->method('consumeActiveForUserAndType');

        $repository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(TokenRequest::class), true);

        $service = new TokenRequestService($repository, $tokenIssuer, $tokenHasher);

        $user = $this->createStub(User::class);

        $now = new \DateTimeImmutable('2026-03-03 12:00:00');

        $result = $service->issue($user, TokenRequestType::EMAIL_CONFIRMATION, $now);

        $this->assertSame('raw_token', $result->token);
        $this->assertSame($generatedToken->hash, $result->hash);
    }

    public function testConsumeValidToken(): void
    {
        $repository = $this->createMock(TokenRequestRepository::class);
        $user = $this->createStub(User::class);
        $tokenIssuer = $this->createStub(TokenIssuer::class); // pas utilisé ici
        $tokenHasher = $this->createMock(TokenHasher::class);

        $request = (new TokenRequest())
            ->setUser($user)
            ->setType(TokenRequestType::EMAIL_CONFIRMATION)
            ->setTokenHash('hash')
            ->setExpiresAt(new \DateTimeImmutable('+1 hour'))
            ->setCreatedAt(new \DateTimeImmutable());

        $tokenHasher
            ->expects($this->once())
            ->method('hash')
            ->with('raw')
            ->willReturn('hash');

        $repository
            ->expects($this->once())
            ->method('findOneByTokenHashAndType')
            ->with('hash', TokenRequestType::EMAIL_CONFIRMATION)
            ->willReturn($request);

        $repository
            ->expects($this->once())
            ->method('save')
            ->with($request, true);

        $service = new TokenRequestService($repository, $tokenIssuer, $tokenHasher, 3600);

        $result = $service->consume('raw', TokenRequestType::EMAIL_CONFIRMATION, new \DateTimeImmutable());

        $this->assertTrue($result->isConsumed());
        $this->assertNotNull($result->getConsumedAt());
    }

    public function testConsumeExpiredTokenThrows(): void
    {
        $repository = $this->createMock(TokenRequestRepository::class);
        $user = $this->createStub(User::class);
        $tokenIssuer = $this->createStub(TokenIssuer::class); // pas utilisé
        $tokenHasher = $this->createMock(TokenHasher::class);

        $now = new \DateTimeImmutable('2026-03-03 12:00:00');

        $request = (new TokenRequest())
            ->setUser($user)
            ->setType(TokenRequestType::EMAIL_CONFIRMATION)
            ->setTokenHash('hash')
            ->setExpiresAt($now->modify('-1 second')) // expiré
            ->setCreatedAt($now->modify('-2 hours'));

        $tokenHasher
            ->expects($this->once())
            ->method('hash')
            ->with('raw')
            ->willReturn('hash');

        $repository
            ->expects($this->once())
            ->method('findOneByTokenHashAndType')
            ->with('hash', TokenRequestType::EMAIL_CONFIRMATION)
            ->willReturn($request);

        $repository
            ->expects($this->never())
            ->method('save');

        $service = new TokenRequestService($repository, $tokenIssuer, $tokenHasher, 3600);

        $this->expectException(InvalidTokenException::class);
        $this->expectExceptionMessage('Invalid token.');

        $service->consume('raw', TokenRequestType::EMAIL_CONFIRMATION, $now);
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function testConsumeAlreadyConsumedTokenThrows(): void
    {
        $repository = $this->createMock(TokenRequestRepository::class);
        $user = $this->createStub(User::class);
        $tokenIssuer = $this->createStub(TokenIssuer::class); // pas utilisé
        $tokenHasher = $this->createMock(TokenHasher::class);

        $now = new \DateTimeImmutable('2026-03-03 12:00:00');

        $request = (new TokenRequest())
            ->setUser($user)
            ->setType(TokenRequestType::EMAIL_CONFIRMATION)
            ->setTokenHash('hash')
            ->setExpiresAt($now->modify('+1 hour'))
            ->setCreatedAt($now->modify('-2 hours'))
            ->setConsumedAt(new \DateTimeImmutable('-1 minute')); // déjà consommé

        $tokenHasher
            ->expects($this->once())
            ->method('hash')
            ->with('raw')
            ->willReturn('hash');

        $repository
            ->expects($this->once())
            ->method('findOneByTokenHashAndType')
            ->with('hash', TokenRequestType::EMAIL_CONFIRMATION)
            ->willReturn($request);

        $repository
            ->expects($this->never())
            ->method('save');

        $service = new TokenRequestService($repository, $tokenIssuer, $tokenHasher, 3600);

        $this->expectException(InvalidTokenException::class);
        $this->expectExceptionMessage('Invalid token.');

        $service->consume('raw', TokenRequestType::EMAIL_CONFIRMATION, $now);
    }
}
