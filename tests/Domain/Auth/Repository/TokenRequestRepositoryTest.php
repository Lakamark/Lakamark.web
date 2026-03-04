<?php

namespace App\Tests\Domain\Auth\Repository;

use App\Domain\Auth\Entity\TokenRequest;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Enum\TokenRequestType;
use App\Domain\Auth\Repository\TokenRequestRepository;
use App\Tests\FixturesLoaderTrait;
use App\Tests\RepositoryTestCase;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Random\RandomException;

class TokenRequestRepositoryTest extends RepositoryTestCase
{
    use FixturesLoaderTrait;

    protected string $repositoryClass = TokenRequestRepository::class;

    /**
     * @throws OptimisticLockException
     * @throws RandomException
     * @throws ORMException
     */
    public function testFindActiveForUserAndTypeReturnsActiveToken(): void
    {
        /** @var User $user */
        ['banned_user' => $user] = $this->loadFixtures(['users']);

        $token = $this->createTokenRequest(
            user: $user,
            type: TokenRequestType::EMAIL_CONFIRMATION,
            expiresAt: new \DateTimeImmutable('+1 hour')
        );

        $this->em->persist($token);
        $this->em->flush();

        $userId = $user->getId();

        $this->em->clear();

        $user = $this->em->getRepository(User::class)->find($userId);

        $found = $this->repository->findActiveForUserAndType(
            $user,
            TokenRequestType::EMAIL_CONFIRMATION,
            new \DateTimeImmutable(),
        );

        $this->assertNotNull($found);
        $this->assertSame($token->getTokenHash(), $found->getTokenHash());
    }

    /**
     * @throws OptimisticLockException
     * @throws RandomException
     * @throws ORMException
     */
    public function testFindActiveForUserAndTypeDoesNotReturnExpiredToken(): void
    {
        $this->loadFixtures(['users']);

        $user = $this->em->getRepository(User::class)->findOneBy([]);
        $this->assertInstanceOf(User::class, $user);

        $token = $this->createTokenRequest(
            user: $user,
            type: TokenRequestType::EMAIL_CONFIRMATION,
            expiresAt: new \DateTimeImmutable('-1 hour'),
        );

        $this->em->persist($token);
        $this->em->flush();

        $userId = $user->getId();

        $this->em->clear();
        $user = $this->em->getRepository(User::class)->find($userId);

        $found = $this->repository->findActiveForUserAndType(
            $user,
            TokenRequestType::EMAIL_CONFIRMATION,
            new \DateTimeImmutable(),
        );

        $this->assertNull($found);
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
            ->setExpiresAt($expiresAt)
            ->setCreatedAt(new \DateTimeImmutable());
    }
}
