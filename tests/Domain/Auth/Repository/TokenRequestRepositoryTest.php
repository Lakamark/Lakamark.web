<?php

namespace App\Tests\Domain\Auth\Repository;

use App\Domain\Auth\Contract\TokenRequestRepositoryInterface;
use App\Domain\Auth\Entity\TokenRequest;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Enum\TokenRequestType;
use App\Tests\FixturesLoaderTrait;
use App\Tests\KernelTestCase;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Random\RandomException;

final class TokenRequestRepositoryTest extends KernelTestCase
{
    use FixturesLoaderTrait;
    private TokenRequestRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var TokenRequestRepositoryInterface $repo */
        $repo = $this->em->getRepository(TokenRequest::class);

        $this->repository = $repo;
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function testFindByTokenHashAndTypeReturnsTokenEvenIfExpired(): void
    {
        $this->loadFixtures(['users']);

        $user = $this->em->getRepository(User::class)->findOneBy([]);
        $this->assertInstanceOf(User::class, $user);

        $token = $this->createTokenRequest(
            user: $user,
            type: TokenRequestType::EMAIL_CONFIRMATION,
            expiresAt: new \DateTimeImmutable('-1 hour'),
        );
        $hash = $token->getTokenHash();

        $this->em->persist($token);
        $this->em->flush();
        $this->em->clear();

        $found = $this->repository->findByTokenHashAndType($hash, TokenRequestType::EMAIL_CONFIRMATION);

        $this->assertNotNull($found);
        $this->assertSame($hash, $found->getTokenHash());
        $this->assertSame(TokenRequestType::EMAIL_CONFIRMATION, $found->getType());
    }

    /**
     * @throws \DateMalformedStringException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function testFindConsumableByTokenHashAndTypeReturnsActiveToken(): void
    {
        $this->loadFixtures(['users']);

        $user = $this->em->getRepository(User::class)->findOneBy([]);
        $this->assertInstanceOf(User::class, $user);

        $now = new \DateTimeImmutable('now');
        $token = $this->createTokenRequest(
            user: $user,
            type: TokenRequestType::EMAIL_CONFIRMATION,
            expiresAt: $now->modify('+1 hour'),
        );
        $hash = $token->getTokenHash();

        $this->em->persist($token);
        $this->em->flush();
        $this->em->clear();

        $found = $this->repository->findConsumableByTokenHashAndType(
            $hash,
            TokenRequestType::EMAIL_CONFIRMATION,
            $now,
        );

        $this->assertNotNull($found);
        $this->assertSame($hash, $found->getTokenHash());
        $this->assertSame(TokenRequestType::EMAIL_CONFIRMATION, $found->getType());
        $this->assertNull($found->getConsumedAt());
    }

    /**
     * @throws OptimisticLockException
     * @throws \DateMalformedStringException
     * @throws ORMException
     */
    public function testFindConsumableByTokenHashAndTypeDoesNotReturnExpiredToken(): void
    {
        $this->loadFixtures(['users']);

        $user = $this->em->getRepository(User::class)->findOneBy([]);
        $this->assertInstanceOf(User::class, $user);

        $now = new \DateTimeImmutable('now');
        $token = $this->createTokenRequest(
            user: $user,
            type: TokenRequestType::EMAIL_CONFIRMATION,
            expiresAt: $now->modify('-1 hour'),
        );
        $hash = $token->getTokenHash();

        $this->em->persist($token);
        $this->em->flush();
        $this->em->clear();

        $found = $this->repository->findConsumableByTokenHashAndType(
            $hash,
            TokenRequestType::EMAIL_CONFIRMATION,
            $now,
        );

        $this->assertNull($found);
    }

    /**
     * @throws OptimisticLockException
     * @throws \DateMalformedStringException
     * @throws ORMException
     */
    public function testFindConsumableByTokenHashAndTypeDoesNotReturnConsumedToken(): void
    {
        $this->loadFixtures(['users']);

        $user = $this->em->getRepository(User::class)->findOneBy([]);
        $this->assertInstanceOf(User::class, $user);

        $now = new \DateTimeImmutable('now');

        $token = $this->createTokenRequest(
            user: $user,
            type: TokenRequestType::EMAIL_CONFIRMATION,
            expiresAt: $now->modify('+1 hour'),
        );

        // Mark as consumed
        $token->consume($now);

        $hash = $token->getTokenHash();

        $this->em->persist($token);
        $this->em->flush();
        $this->em->clear();

        $found = $this->repository->findConsumableByTokenHashAndType(
            $hash,
            TokenRequestType::EMAIL_CONFIRMATION,
            $now,
        );

        $this->assertNull($found);
    }

    /**
     * @throws OptimisticLockException
     * @throws \DateMalformedStringException
     * @throws ORMException
     */
    public function testRevokeConsumableForUserAndTypeConsumesOnlyActiveTokens(): void
    {
        $this->loadFixtures(['users']);

        $user = $this->em->getRepository(User::class)->findOneBy([]);
        $this->assertInstanceOf(User::class, $user);

        $now = new \DateTimeImmutable('now');

        // 1) active token (should be revoked)
        $active = $this->createTokenRequest(
            user: $user,
            type: TokenRequestType::EMAIL_CONFIRMATION,
            expiresAt: $now->modify('+2 hours'),
        );

        // 2) expired token (should NOT be revoked)
        $expired = $this->createTokenRequest(
            user: $user,
            type: TokenRequestType::EMAIL_CONFIRMATION,
            expiresAt: $now->modify('-2 hours'),
        );

        // 3) consumed token (should NOT be revoked again)
        $consumed = $this->createTokenRequest(
            user: $user,
            type: TokenRequestType::EMAIL_CONFIRMATION,
            expiresAt: $now->modify('+2 hours'),
        );
        $consumed->consume($now);

        $this->em->persist($active);
        $this->em->persist($expired);
        $this->em->persist($consumed);
        $this->em->flush();
        $this->em->clear();

        $count = $this->repository->revokeConsumableForUserAndType(
            $user->getId(),
            TokenRequestType::EMAIL_CONFIRMATION,
            $now,
        );

        // Only the single active token should be affected
        $this->assertSame(1, $count);

        // Reload & assert states
        $reloadedActive = $this->repository->findByTokenHashAndType(
            $active->getTokenHash(),
            TokenRequestType::EMAIL_CONFIRMATION
        );
        $this->assertNotNull($reloadedActive);
        $this->assertNotNull($reloadedActive->getConsumedAt());

        $reloadedExpired = $this->repository->findByTokenHashAndType(
            $expired->getTokenHash(),
            TokenRequestType::EMAIL_CONFIRMATION
        );
        $this->assertNotNull($reloadedExpired);
        $this->assertNull($reloadedExpired->getConsumedAt());

        $reloadedConsumed = $this->repository->findByTokenHashAndType(
            $consumed->getTokenHash(),
            TokenRequestType::EMAIL_CONFIRMATION
        );
        $this->assertNotNull($reloadedConsumed);
        $this->assertNotNull($reloadedConsumed->getConsumedAt());
    }

    /**
     * @throws RandomException
     */
    private function createTokenRequest(
        User $user,
        TokenRequestType $type,
        \DateTimeImmutable $expiresAt,
    ): TokenRequest {
        return (new TokenRequest())
            ->setUser($user)
            ->setType($type)
            ->setExpiresAt($expiresAt)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setTokenHash(hash('sha256', bin2hex(random_bytes(32))));
    }

    /**
     * @throws \DateMalformedStringException
     * @throws ORMException
     */
    public function testRevokeConsumableForUserAndTypeConsumesOnlyConsumableTokens(): void
    {
        $this->loadFixtures(['users']);

        $user = $this->em->getRepository(User::class)->findOneBy([]);
        $this->assertInstanceOf(User::class, $user);
        $userId = $user->getId();

        $repo = self::getContainer()->get(TokenRequestRepositoryInterface::class);
        $now = new \DateTimeImmutable('2026-03-03 12:00:00');

        // Consumable (should be revoked)
        $active1 = (new TokenRequest())
            ->setUser($user)
            ->setType(TokenRequestType::EMAIL_CONFIRMATION)
            ->setTokenHash(str_repeat('a', 64))
            ->setCreatedAt($now->modify('-10 minutes'))
            ->setExpiresAt($now->modify('+1 hour'));

        // Consumable (should be revoked)
        $active2 = (new TokenRequest())
            ->setUser($user)
            ->setType(TokenRequestType::EMAIL_CONFIRMATION)
            ->setTokenHash(str_repeat('b', 64))
            ->setCreatedAt($now->modify('-20 minutes'))
            ->setExpiresAt($now->modify('+2 hours'));

        // Expired (should NOT be revoked)
        $expired = (new TokenRequest())
            ->setUser($user)
            ->setType(TokenRequestType::EMAIL_CONFIRMATION)
            ->setTokenHash(str_repeat('c', 64))
            ->setCreatedAt($now->modify('-3 hours'))
            ->setExpiresAt($now->modify('-1 minute'));

        // Already consumed (should NOT be revoked)
        $consumed = (new TokenRequest())
            ->setUser($user)
            ->setType(TokenRequestType::EMAIL_CONFIRMATION)
            ->setTokenHash(str_repeat('d', 64))
            ->setCreatedAt($now->modify('-30 minutes'))
            ->setExpiresAt($now->modify('+1 hour'))
            ->setConsumedAt($now->modify('-5 minutes'));

        $this->em->persist($active1);
        $this->em->persist($active2);
        $this->em->persist($expired);
        $this->em->persist($consumed);
        $this->em->flush();
        $this->em->clear();

        $count = $repo->revokeConsumableForUserAndType($userId, TokenRequestType::EMAIL_CONFIRMATION, $now);

        // Assert: only 2 active should be affected
        $this->assertSame(2, $count);

        // Reload and assert each state
        $this->em->clear();

        $r1 = $this->em->getRepository(TokenRequest::class)->findOneBy(['tokenHash' => str_repeat('a', 64)]);
        $r2 = $this->em->getRepository(TokenRequest::class)->findOneBy(['tokenHash' => str_repeat('b', 64)]);
        $r3 = $this->em->getRepository(TokenRequest::class)->findOneBy(['tokenHash' => str_repeat('c', 64)]);
        $r4 = $this->em->getRepository(TokenRequest::class)->findOneBy(['tokenHash' => str_repeat('d', 64)]);

        $this->assertNotNull($r1);
        $this->assertNotNull($r2);
        $this->assertNotNull($r3);
        $this->assertNotNull($r4);
        // revoked tokens
        $this->assertTrue($r1->isConsumed());
        $this->assertEquals($now, $r1->getConsumedAt());

        $this->assertTrue($r2->isConsumed());
        $this->assertEquals($now, $r2->getConsumedAt());

        // untouched tokens
        $this->assertFalse($r3->isConsumed()); // expired stays unconsumed
        $this->assertTrue($r4->isConsumed());  // already consumed stays consumed
    }
}
