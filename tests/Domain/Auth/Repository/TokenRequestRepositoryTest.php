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

        /** @var TokenRequestRepositoryInterface $repository */
        $repository = $this->em->getRepository(TokenRequest::class);
        $this->repository = $repository;
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws RandomException
     */
    public function testSavePersistsTokenRequest(): void
    {
        $this->loadFixtures(['users']);

        $user = $this->em->getRepository(User::class)->findOneBy([]);
        $this->assertInstanceOf(User::class, $user);

        $token = $this->createTokenRequest(
            user: $user,
            type: TokenRequestType::EMAIL_CONFIRMATION,
            expiresAt: new \DateTimeImmutable('+1 hour'),
        );

        $hash = $token->getTokenHash();

        $this->repository->save($token, true);
        $this->em->clear();

        $found = $this->repository->findByTokenHashAndType(
            $hash,
            TokenRequestType::EMAIL_CONFIRMATION,
        );

        $this->assertNotNull($found);
        $this->assertSame($hash, $found->getTokenHash());
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws RandomException
     */
    public function testFindByTokenHashAndTypeReturnsMatchingToken(): void
    {
        $this->loadFixtures(['users']);

        $user = $this->em->getRepository(User::class)->findOneBy([]);
        $this->assertInstanceOf(User::class, $user);

        $token = $this->createTokenRequest(
            user: $user,
            type: TokenRequestType::EMAIL_CONFIRMATION,
            expiresAt: new \DateTimeImmutable('+1 hour'),
        );

        $hash = $token->getTokenHash();

        $this->em->persist($token);
        $this->em->flush();
        $this->em->clear();

        $found = $this->repository->findByTokenHashAndType(
            $hash,
            TokenRequestType::EMAIL_CONFIRMATION,
        );

        $this->assertNotNull($found);
        $this->assertSame($hash, $found->getTokenHash());
        $this->assertSame(TokenRequestType::EMAIL_CONFIRMATION, $found->getType());
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws RandomException
     */
    public function testFindByTokenHashAndTypeReturnsNullForUnknownHash(): void
    {
        $this->loadFixtures(['users']);

        $user = $this->em->getRepository(User::class)->findOneBy([]);
        $this->assertInstanceOf(User::class, $user);

        $token = $this->createTokenRequest(
            user: $user,
            type: TokenRequestType::EMAIL_CONFIRMATION,
            expiresAt: new \DateTimeImmutable('+1 hour'),
        );

        $this->em->persist($token);
        $this->em->flush();
        $this->em->clear();

        $found = $this->repository->findByTokenHashAndType(
            str_repeat('f', 64),
            TokenRequestType::EMAIL_CONFIRMATION,
        );

        $this->assertNull($found);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws RandomException
     */
    public function testFindByTokenHashAndTypeReturnsNullForWrongType(): void
    {
        $this->loadFixtures(['users']);

        $user = $this->em->getRepository(User::class)->findOneBy([]);
        $this->assertInstanceOf(User::class, $user);

        $token = $this->createTokenRequest(
            user: $user,
            type: TokenRequestType::EMAIL_CONFIRMATION,
            expiresAt: new \DateTimeImmutable('+1 hour'),
        );

        $hash = $token->getTokenHash();

        $this->em->persist($token);
        $this->em->flush();
        $this->em->clear();

        $found = $this->repository->findByTokenHashAndType(
            $hash,
            TokenRequestType::PASSWORD_RESET,
        );

        $this->assertNull($found);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws RandomException
     */
    public function testFindByTokenHashAndTypeReturnsExpiredToken(): void
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

        $found = $this->repository->findByTokenHashAndType(
            $hash,
            TokenRequestType::EMAIL_CONFIRMATION,
        );

        $this->assertNotNull($found);
        $this->assertSame($hash, $found->getTokenHash());
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws RandomException
     * @throws \DateMalformedStringException
     */
    public function testFindByTokenHashAndTypeReturnsConsumedToken(): void
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
        $token->consume($now);

        $hash = $token->getTokenHash();

        $this->em->persist($token);
        $this->em->flush();
        $this->em->clear();

        $found = $this->repository->findByTokenHashAndType(
            $hash,
            TokenRequestType::EMAIL_CONFIRMATION,
        );

        $this->assertNotNull($found);
        $this->assertSame($hash, $found->getTokenHash());
        $this->assertNotNull($found->getConsumedAt());
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \DateMalformedStringException
     */
    public function testFindUsableForUserAndTypeReturnsOnlyUsableTokens(): void
    {
        $this->loadFixtures(['users']);

        $users = $this->em->getRepository(User::class)->findAll();
        $this->assertGreaterThanOrEqual(2, count($users));

        /** @var User $user */
        $user = $users[0];

        /** @var User $otherUser */
        $otherUser = $users[1];

        $this->assertInstanceOf(User::class, $user);
        $this->assertInstanceOf(User::class, $otherUser);
        $this->assertNotSame($user->getId(), $otherUser->getId());

        $userId = $user->getId();

        $now = new \DateTimeImmutable('2026-03-10 12:00:00');

        $usable1 = (new TokenRequest())
            ->setUser($user)
            ->setType(TokenRequestType::EMAIL_CONFIRMATION)
            ->setTokenHash(str_repeat('a', 64))
            ->setCreatedAt($now->modify('-10 minutes'))
            ->setExpiresAt($now->modify('+1 hour'));

        $usable2 = (new TokenRequest())
            ->setUser($user)
            ->setType(TokenRequestType::EMAIL_CONFIRMATION)
            ->setTokenHash(str_repeat('b', 64))
            ->setCreatedAt($now->modify('-20 minutes'))
            ->setExpiresAt($now->modify('+2 hours'));

        $expired = (new TokenRequest())
            ->setUser($user)
            ->setType(TokenRequestType::EMAIL_CONFIRMATION)
            ->setTokenHash(str_repeat('c', 64))
            ->setCreatedAt($now->modify('-3 hours'))
            ->setExpiresAt($now->modify('-1 minute'));

        $consumed = (new TokenRequest())
            ->setUser($user)
            ->setType(TokenRequestType::EMAIL_CONFIRMATION)
            ->setTokenHash(str_repeat('d', 64))
            ->setCreatedAt($now->modify('-30 minutes'))
            ->setExpiresAt($now->modify('+1 hour'));

        $consumed->consume($now->modify('-5 minutes'));

        $revoked = (new TokenRequest())
            ->setUser($user)
            ->setType(TokenRequestType::EMAIL_CONFIRMATION)
            ->setTokenHash(str_repeat('e', 64))
            ->setCreatedAt($now->modify('-40 minutes'))
            ->setExpiresAt($now->modify('+1 hour'));

        $revoked->revoke($now->modify('-2 minutes'));

        $wrongType = (new TokenRequest())
            ->setUser($user)
            ->setType(TokenRequestType::PASSWORD_RESET)
            ->setTokenHash(str_repeat('f', 64))
            ->setCreatedAt($now->modify('-15 minutes'))
            ->setExpiresAt($now->modify('+1 hour'));

        $otherUserToken = (new TokenRequest())
            ->setUser($otherUser)
            ->setType(TokenRequestType::EMAIL_CONFIRMATION)
            ->setTokenHash(str_repeat('g', 64))
            ->setCreatedAt($now->modify('-12 minutes'))
            ->setExpiresAt($now->modify('+1 hour'));

        $this->em->persist($usable1);
        $this->em->persist($usable2);
        $this->em->persist($expired);
        $this->em->persist($consumed);
        $this->em->persist($revoked);
        $this->em->persist($wrongType);
        $this->em->persist($otherUserToken);
        $this->em->flush();
        $this->em->clear();

        /** @var User|null $user */
        $user = $this->em->getRepository(User::class)->find($userId);
        $this->assertInstanceOf(User::class, $user);

        $results = $this->repository->findUsableForUserAndType(
            $user,
            TokenRequestType::EMAIL_CONFIRMATION,
            $now,
        );

        $this->assertCount(2, $results);

        $hashes = array_map(
            static fn (TokenRequest $tokenRequest): string => $tokenRequest->getTokenHash(),
            $results,
        );

        $this->assertContains(str_repeat('a', 64), $hashes);
        $this->assertContains(str_repeat('b', 64), $hashes);
        $this->assertNotContains(str_repeat('c', 64), $hashes);
        $this->assertNotContains(str_repeat('d', 64), $hashes);
        $this->assertNotContains(str_repeat('e', 64), $hashes);
        $this->assertNotContains(str_repeat('f', 64), $hashes);
        $this->assertNotContains(str_repeat('g', 64), $hashes);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \DateMalformedStringException
     */
    public function testFindUsableForUserAndTypeReturnsEmptyArrayWhenNoUsableTokenExists(): void
    {
        $this->loadFixtures(['users']);

        $user = $this->em->getRepository(User::class)->findOneBy([]);
        $this->assertInstanceOf(User::class, $user);

        $now = new \DateTimeImmutable('2026-03-10 12:00:00');

        $expired = (new TokenRequest())
            ->setUser($user)
            ->setType(TokenRequestType::EMAIL_CONFIRMATION)
            ->setTokenHash(str_repeat('h', 64))
            ->setCreatedAt($now->modify('-2 hours'))
            ->setExpiresAt($now->modify('-1 minute'));

        $consumed = (new TokenRequest())
            ->setUser($user)
            ->setType(TokenRequestType::EMAIL_CONFIRMATION)
            ->setTokenHash(str_repeat('i', 64))
            ->setCreatedAt($now->modify('-30 minutes'))
            ->setExpiresAt($now->modify('+1 hour'));

        $consumed->consume($now->modify('-10 minutes'));

        $revoked = (new TokenRequest())
            ->setUser($user)
            ->setType(TokenRequestType::EMAIL_CONFIRMATION)
            ->setTokenHash(str_repeat('j', 64))
            ->setCreatedAt($now->modify('-30 minutes'))
            ->setExpiresAt($now->modify('+1 hour'));

        $revoked->revoke($now->modify('-5 minutes'));

        $this->em->persist($expired);
        $this->em->persist($consumed);
        $this->em->persist($revoked);
        $this->em->flush();
        $this->em->clear();

        $results = $this->repository->findUsableForUserAndType(
            $user,
            TokenRequestType::EMAIL_CONFIRMATION,
            $now,
        );

        $this->assertSame([], $results);
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
            ->setTokenHash(hash('sha256', bin2hex(random_bytes(32))))
            ->setCreatedAt(new \DateTimeImmutable())
            ->setExpiresAt($expiresAt);
    }
}
