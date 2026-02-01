<?php

namespace App\Tests\Domain\Moderation\Repository;

use App\Domain\Auth\Entity\User;
use App\Domain\Moderation\Entity\UserBan;
use App\Domain\Moderation\Enum\BanReason;
use App\Domain\Moderation\Repository\UserBanRepository;
use App\Tests\FixturesLoaderTrait;
use App\Tests\RepositoryTestCase;
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
}
