<?php

namespace App\Tests\Domain\Moderation\Repository;

use App\Domain\Auth\Entity\User;
use App\Domain\Moderation\Entity\UserBan;
use App\Domain\Moderation\Enum\BanReasonEnum;
use App\Domain\Moderation\Repository\UserBanRepository;
use App\Tests\FixturesLoaderTrait;
use App\Tests\TestCases\RepositoryTestCase;

class UserBanRepositoryTest extends RepositoryTestCase
{
    use FixturesLoaderTrait;

    protected string $repositoryClass = UserBanRepository::class;

    public function testFindBanAndCreateIt(): void
    {
        /** @var User $user */
        ['banned_user_1' => $user] = $this->loadFixtures(['users']);

        $now = new \DateTimeImmutable();

        $ban = (new UserBan())
            ->setUser($user)
            ->setBanReason(BanReasonEnum::BOT)
            ->setDetails('Bot triggered')
            ->setCreatedAt($now->modify('-5 minutes'))
            ->setExpiresAt(null)
            ->setEndedAt(null)
        ;

        $this->em->persist($ban);
        $this->em->flush();
        $this->em->clear();

        $banQuery = $this->repository->findActiveBanFor($user, $now);
        $this->assertNotNull($banQuery);
        $this->assertSame($user->getId(), $banQuery->getUser()->getId());
        $this->assertSame(BanReasonEnum::BOT, $banQuery->getBanReason());
    }

    public function testFindExpiredNotEnded(): void
    {
        ['banned_user_1' => $user] = $this->loadFixtures(['users']);

        $now = new \DateTimeImmutable('2025-01-01 12:00:00');

        $expiredNotEnded = (new UserBan())
            ->setUser($user)
            ->setExpiresAt($now->modify('-1 hour'))
            ->setEndedAt(null)
            ->setBanReason(BanReasonEnum::TERMS_VIOLATION)
            ->setCreatedAt($now->modify('-2 hours'));

        $expiredEnded = (new UserBan())
            ->setUser($user)
            ->setExpiresAt($now->modify('-2 hours'))
            ->setBanReason(BanReasonEnum::TERMS_VIOLATION)
            ->setEndedAt($now->modify('-2 hours'))
            ->setCreatedAt($now->modify('-3 hours'));

        $notExpired = (new UserBan())
            ->setUser($user)
            ->setExpiresAt($now->modify('+1 hour'))
            ->setEndedAt(null)
            ->setBanReason(BanReasonEnum::TERMS_VIOLATION)
            ->setCreatedAt($now->modify('-1 hour'));

        $permanent = (new UserBan())
            ->setUser($user)
            ->setExpiresAt(null)
            ->setEndedAt(null)
            ->setBanReason(BanReasonEnum::BOT)
            ->setCreatedAt($now->modify('-1 day'));

        $this->em->persist($expiredNotEnded);
        $this->em->persist($expiredEnded);
        $this->em->persist($notExpired);
        $this->em->persist($permanent);

        $this->em->flush();
        $this->em->clear();

        $result = $this->repository->findExpiredNotEnded($now);

        $this->assertCount(1, $result);
        $this->assertSame(
            $expiredNotEnded->getId(),
            $result[0]->getId()
        );
    }
}
