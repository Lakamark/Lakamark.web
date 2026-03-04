<?php

namespace App\Tests\Domain\Auth\Service;

use App\Domain\Auth\Contract\TokenRequestRepositoryInterface;
use App\Domain\Auth\Entity\TokenRequest;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Enum\TokenRequestType;
use App\Domain\Auth\Exception\InvalidTokenException;
use App\Domain\Auth\Service\TokenRequestService;
use App\Foundation\Security\GeneratedTokenDTO;
use App\Foundation\Security\TokenIssuer;
use App\Tests\FixturesLoaderTrait;
use App\Tests\KernelTestCase;
use Doctrine\ORM\Exception\ORMException;
use Random\RandomException;

class TokenRequestServiceTest extends KernelTestCase
{
    use FixturesLoaderTrait;

    /**
     * @throws RandomException
     */
    public function testIssueCreatesTokenRequestAndReturnsIssuedDto(): void
    {
        $this->loadFixtures(['users']);

        $user = $this->em->getRepository(User::class)->findOneBy([]);
        $this->assertInstanceOf(User::class, $user);
        $userId = $user->getId();

        $now = new \DateTimeImmutable('2026-03-03 12:00:00');

        $repo = self::getContainer()->get(TokenRequestRepositoryInterface::class);

        // Mock issuer
        $issuer = $this->createMock(TokenIssuer::class);
        $generated = new GeneratedTokenDTO(
            token: 'raw_token',
            hash: str_repeat('a', 64),
        );

        $issuer->expects($this->once())
            ->method('issue')
            ->willReturn($generated);

        $service = new TokenRequestService($repo, $issuer);

        // ACT
        $result = $service->issue($user, TokenRequestType::EMAIL_CONFIRMATION, $now);

        $hash = $result->issued->hash;

        // Assert return (values only)
        $this->assertSame('raw_token', $result->issued->token);
        $this->assertSame(str_repeat('a', 64), $result->issued->hash);

        // Assert DB (ALWAYS use result->issued->hash)
        $this->em->clear();

        $saved = $this->em->getRepository(TokenRequest::class)->findOneBy([
            'tokenHash' => $result->issued->hash,
            'type' => TokenRequestType::EMAIL_CONFIRMATION,
        ]);

        $this->assertNotNull($saved);
        $this->assertSame($userId, $saved->getUser()->getId());
        $this->assertSame($hash, $saved->getTokenHash());
        $this->assertEquals($now, $saved->getCreatedAt());
    }

    /**
     * @throws \DateMalformedStringException
     * @throws ORMException
     */
    public function testConsumeValidTokenMarksConsumedAndPersists(): void
    {
        $this->loadFixtures(['users']);
        $user = $this->em->getRepository(User::class)->findOneBy([]);

        $repo = self::getContainer()->get(TokenRequestRepositoryInterface::class);
        $issuer = $this->createStub(TokenIssuer::class);

        $service = new TokenRequestService($repo, $issuer);

        $now = new \DateTimeImmutable('2026-03-03 12:00:00');
        $hash = str_repeat('b', 64);

        // Arrange: create a consumable token request in DB
        $request = (new TokenRequest())
            ->setUser($user)
            ->setType(TokenRequestType::EMAIL_CONFIRMATION)
            ->setTokenHash($hash)
            ->setCreatedAt($now->modify('-10 minutes'))
            ->setExpiresAt($now->modify('+1 hour'));

        $this->em->persist($request);
        $this->em->flush();
        $this->em->clear();

        $consumed = $service->consume($hash, TokenRequestType::EMAIL_CONFIRMATION, $now);

        $this->assertTrue($consumed->isConsumed());
        $this->assertEquals($now, $consumed->getConsumedAt());

        $this->em->clear();

        $saved = $this->em->getRepository(TokenRequest::class)->findOneBy([
            'tokenHash' => $hash,
            'type' => TokenRequestType::EMAIL_CONFIRMATION,
        ]);

        $this->assertNotNull($saved);
        $this->assertTrue($saved->isConsumed());
        $this->assertEquals($now, $saved->getConsumedAt());
    }

    /**
     * @throws \DateMalformedStringException
     * @throws ORMException
     */
    public function testConsumeExpiredTokenThrows(): void
    {
        $this->loadFixtures(['users']);

        $user = $this->em->getRepository(User::class)->findOneBy([]);
        $this->assertInstanceOf(User::class, $user);

        $repo = self::getContainer()->get(TokenRequestRepositoryInterface::class);
        $issuer = $this->createStub(TokenIssuer::class);

        $service = new TokenRequestService($repo, $issuer);

        $now = new \DateTimeImmutable('2026-03-03 12:00:00');
        $hash = str_repeat('c', 64);

        $request = (new TokenRequest())
            ->setUser($user)
            ->setType(TokenRequestType::EMAIL_CONFIRMATION)
            ->setTokenHash($hash)
            ->setCreatedAt($now->modify('-2 hours'))
            ->setExpiresAt($now->modify('-1 minute')); // expired

        $this->em->persist($request);
        $this->em->flush();
        $this->em->clear();

        $this->expectException(InvalidTokenException::class);
        $this->expectExceptionMessage('Invalid token.');

        $service->consume($hash, TokenRequestType::EMAIL_CONFIRMATION, $now);
    }

    /**
     * @throws \DateMalformedStringException
     * @throws ORMException
     */
    public function testConsumeAlreadyConsumedTokenThrows(): void
    {
        $this->loadFixtures(['users']);

        $user = $this->em->getRepository(User::class)->findOneBy([]);
        $this->assertInstanceOf(User::class, $user);

        $repo = self::getContainer()->get(TokenRequestRepositoryInterface::class);
        $issuer = $this->createStub(TokenIssuer::class);

        $service = new TokenRequestService($repo, $issuer);

        $now = new \DateTimeImmutable('2026-03-03 12:00:00');
        $hash = str_repeat('d', 64);

        $request = (new TokenRequest())
            ->setUser($user)
            ->setType(TokenRequestType::EMAIL_CONFIRMATION)
            ->setTokenHash($hash)
            ->setCreatedAt($now->modify('-10 minutes'))
            ->setExpiresAt($now->modify('+1 hour'))
            ->setConsumedAt($now->modify('-1 minute')); // already consumed

        $this->em->persist($request);
        $this->em->flush();
        $this->em->clear();

        $this->expectException(InvalidTokenException::class);
        $this->expectExceptionMessage('Invalid token.');

        $service->consume($hash, TokenRequestType::EMAIL_CONFIRMATION, $now);
    }

    /**
     * @throws RandomException
     * @throws \DateMalformedStringException
     */
    public function testIssueRevokesPreviousActiveToken(): void
    {
        $this->loadFixtures(['users']);

        $user = $this->em->getRepository(User::class)->findOneBy([]);
        $this->assertInstanceOf(User::class, $user);
        $userId = $user->getId();

        $repo = self::getContainer()->get(TokenRequestRepositoryInterface::class);

        $issuer = $this->createStub(TokenIssuer::class);
        $issuer->method('issue')->willReturnOnConsecutiveCalls(
            new GeneratedTokenDTO('token1', str_repeat('a', 64)),
            new GeneratedTokenDTO('token2', str_repeat('b', 64))
        );

        $service = new TokenRequestService($repo, $issuer);

        $now = new \DateTimeImmutable('2026-03-03 12:00:00');

        // First issue
        $service->issue(
            $user,
            TokenRequestType::EMAIL_CONFIRMATION,
            $now
        );

        // Second issue
        $service->issue(
            $user,
            TokenRequestType::EMAIL_CONFIRMATION,
            $now->modify('+1 minute')
        );

        $this->em->clear();

        $repoDoctrine = $this->em->getRepository(TokenRequest::class);

        $token1 = $repoDoctrine->findOneBy(['tokenHash' => str_repeat('a', 64)]);
        $token2 = $repoDoctrine->findOneBy(['tokenHash' => str_repeat('b', 64)]);

        $this->assertNotNull($token1);
        $this->assertNotNull($token2);

        // First token should now be consumed
        $this->assertTrue($token1->isConsumed());

        // Second token should remain active
        $this->assertFalse($token2->isConsumed());

        // Both belong to same user
        $this->assertSame($userId, $token1->getUser()->getId());
        $this->assertSame($userId, $token2->getUser()->getId());
    }
}
