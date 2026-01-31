<?php

namespace App\Tests\Domain\Moderation\Repository;

use App\Domain\Auth\Entity\User;
use App\Domain\Moderation\Entity\UserBan;
use App\Domain\Moderation\Enum\BanReason;
use App\Domain\Moderation\Repository\UserBanRepository;
use App\Tests\FixturesLoaderTrait;
use App\Tests\RepositoryTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

class UserBanRepositoryTest extends RepositoryTestCase
{
    use FixturesLoaderTrait;

    protected string $repositoryClass = UserBanRepository::class;

    /**
     * @throws ORMException|\DateMalformedStringException
     */
    public function testFindBanAndCreate(): void
    {
        /** @var User $user */
        ['banned_user' => $user] = $this->loadFixtures(['users']);

        $now = new \DateTimeImmutable('2026-01-31 11:34:00');

        $ban = (new UserBan())
            ->setUser($user)
            ->setBanReason(BanReason::BOT)
            ->setDetails('Bot triggered')
            ->setCreatedAt($now->modify('-5 minutes'))
            ->setExpiresAt(null)
            ->setEndedAt(null);

        // Persist the ban in the table.
        $this->em->persist($ban);
        $this->em->flush();
        $this->em->clear();

        // Find the record.
        $banQuery = $this->repository->findFor($user, $now);
        $this->assertNotNull($banQuery);
        $this->assertSame($user->getId(), $banQuery->getUser()->getId());
        $this->assertSame(BanReason::BOT, $banQuery->getBanReason());
    }

    /**
     * @throws ORMException
     * @throws \DateMalformedStringException
     * @throws OptimisticLockException
     */
    public function testFindExpiredNotEnded(): void
    {
        /** @var User $user */
        ['banned_user' => $user] = $this->loadFixtures(['users']);

        $now = new \DateTimeImmutable('2026-01-31 11:34:00');

        // Ban types
        $expiredNotEnded = (new UserBan())
            ->setUser($user)
            ->setExpiresAt($now->modify('-1 hour'))
            ->setEndedAt(null)
            ->setBanReason(BanReason::TERMS_VIOLATION)
            ->setCreatedAt($now->modify('-2 hours'));

        $expiredEnded = (new UserBan())
            ->setUser($user)
            ->setExpiresAt($now->modify('-2 hours'))
            ->setBanReason(BanReason::TERMS_VIOLATION)
            ->setEndedAt($now->modify('-2 hours'))
            ->setCreatedAt($now->modify('-3 hours'));

        $notExpired = (new UserBan())
            ->setUser($user)
            ->setExpiresAt($now->modify('+1 hour'))
            ->setEndedAt(null)
            ->setBanReason(BanReason::TERMS_VIOLATION)
            ->setCreatedAt($now->modify('-1 hour'));

        $permanent = (new UserBan())
            ->setUser($user)
            ->setExpiresAt(null)
            ->setEndedAt(null)
            ->setBanReason(BanReason::BOT)
            ->setCreatedAt($now->modify('-1 day'));

        // Save the records.
        $this->em->persist($expiredNotEnded);
        $this->em->persist($expiredEnded);
        $this->em->persist($notExpired);
        $this->em->persist($permanent);

        $this->em->flush();
        $expiredNotEndedId = $expiredNotEnded->getId();
        $this->em->clear();

        // Execute the query.
        $result = $this->repository->findExpiredNotEnded($now);
        $this->assertCount(1, $result);
        $this->assertSame($expiredNotEndedId, $result[0]->getId());
    }

    /**
     * @throws \DateMalformedStringException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function testFindActiveBanIgnoresExpiredNotEnded(): void
    {
        /** @var User $user */
        ['banned_user' => $user] = $this->loadFixtures(['users']);
        $now = new \DateTimeImmutable('2026-01-31 11:34:00');

        $expiredNotEnded = (new UserBan())
            ->setUser($user)
            ->setBanReason(BanReason::BOT)
            ->setCreatedAt($now->modify('-2 hours'))
            ->setExpiresAt($now->modify('-1 hour'))
            ->setEndedAt(null);

        $this->em->persist($expiredNotEnded);
        $this->em->flush();
        $this->em->clear();

        $ban = $this->repository->findActiveBanFor($user, $now);
        $this->assertNull($ban);
    }

    public function testFindActiveBanReturnsMostRecentActive(): void
    {
        /** @var User $user */
        ['banned_user' => $user] = $this->loadFixtures(['users']);
        $now = new \DateTimeImmutable('2026-01-31 11:34:00');

        $older = (new UserBan())
            ->setUser($user)
            ->setBanReason(BanReason::BOT)
            ->setCreatedAt($now->modify('-2 hours'))
            ->setExpiresAt(null)
            ->setEndedAt(null);

        $newer = (new UserBan())
            ->setUser($user)
            ->setBanReason(BanReason::SPAM)
            ->setCreatedAt($now->modify('-10 minutes'))
            ->setExpiresAt(null)
            ->setEndedAt(null);

        $this->em->persist($older);
        $this->em->persist($newer);
        $this->em->flush();
        $this->em->clear();

        $ban = $this->repository->findActiveBanFor($user, $now);

        $this->assertNotNull($ban);
        $this->assertSame(BanReason::SPAM, $ban->getBanReason());
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function testCountExpiredBans(): void
    {
        /** @var User $user */
        ['banned_user' => $user] = $this->loadFixtures(['users']);

        $now = new \DateTimeImmutable('2026-01-31 11:34:00');

        // should be returned: expired + non-BOT + not ended
        $closable = $this->ban(
            user: $user,
            reason: BanReason::SPAM,
            createdAt: new \DateTimeImmutable('2026-01-01 10:00:00'),
            expiresAt: new \DateTimeImmutable('2026-01-10 10:00:00'),
            endedAt: null,
        );

        // should NOT be returned: expired but BOT
        $expiredBot = $this->ban(
            user: $user,
            reason: BanReason::BOT,
            createdAt: new \DateTimeImmutable('2026-01-01 10:00:00'),
            expiresAt: new \DateTimeImmutable('2026-01-10 10:00:00'),
            endedAt: null,
        );

        // should NOT be returned: not expired
        $active = $this->ban(
            user: $user,
            reason: BanReason::SPAM,
            createdAt: new \DateTimeImmutable('2026-01-30 10:00:00'),
            expiresAt: new \DateTimeImmutable('2026-02-10 10:00:00'),
            endedAt: null,
        );

        // should NOT be returned: expired but already ended
        $alreadyEnded = $this->ban(
            user: $user,
            reason: BanReason::SPAM,
            createdAt: new \DateTimeImmutable('2026-01-01 10:00:00'),
            expiresAt: new \DateTimeImmutable('2026-01-10 10:00:00'),
            endedAt: new \DateTimeImmutable('2026-01-05 10:00:00'),
        );

        // should NOT be returned: permanent ban (expiresAt null)
        $permanent = $this->ban(
            user: $user,
            reason: BanReason::SPAM,
            createdAt: new \DateTimeImmutable('2026-01-01 10:00:00'),
            expiresAt: null,
            endedAt: null,
        );

        $this->em->persist($closable);
        $this->em->persist($expiredBot);
        $this->em->persist($active);
        $this->em->persist($alreadyEnded);
        $this->em->persist($permanent);

        $this->em->flush();
        $this->em->clear();

        /** @var UserBanRepository $results */
        $repo = $this->getEntityManager()->getRepository(UserBan::class);
        $result = $this->repository->findExpiredClosableBans($now);
        $this->assertCount(1, $result);
        $this->assertSame($closable->getId(), $result[0]->getId());
    }

    private function ban(User $user,
        BanReason $reason,
        \DateTimeImmutable $createdAt,
        ?\DateTimeImmutable $expiresAt,
        ?\DateTimeImmutable $endedAt): UserBan
    {
        return (new UserBan())
            ->setUser($user)
            ->setBanReason($reason)
            ->setDetails('Test')
            ->setCreatedAt($createdAt)
            ->setExpiresAt($expiresAt)
            ->setEndedAt($endedAt);
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return self::getContainer()->get(EntityManagerInterface::class);
    }
}
