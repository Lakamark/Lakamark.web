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

    public function testFindBan(): void
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
}
