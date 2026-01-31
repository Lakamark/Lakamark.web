<?php

namespace App\Tests\Command;

use App\Domain\Auth\Entity\User;
use App\Domain\Moderation\Entity\UserBan;
use App\Domain\Moderation\Enum\BanReason;
use App\Tests\CommandTestCase;
use App\Tests\FixturesLoaderTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Tester\CommandTester;

class CloseExpiredBansCommandTest extends CommandTestCase
{
    use FixturesLoaderTrait;

    public function testCloseExpiredBansWithoutBotBans(): void
    {
        /** @var User $user */
        ['banned_user' => $user] = $this->loadFixtures(['users']);

        $this->entityManager();

        // Ban types
        $expiredNonBot = $this->expiredBan($user, BanReason::BOT);
        $expiredBot = $this->expiredBan($user, BanReason::BOT);
        $activeBan = $this->activeBan($user);

        $this->persist($expiredNonBot, $expiredBot, $activeBan);

        $this->runCloseBanCommand();

        $this->assertBanClosedByExpiration($expiredNonBot);
        $this->assertBanStillActive($expiredBot);
        $this->assertBanStillActive($activeBan);
    }

    private function expiredBan(User $user, BanReason $reason): UserBan
    {
        return (new UserBan())
            ->setUser($user)
            ->setBanReason($reason)
            ->setCreatedAt(new \DateTimeImmutable('2026-01-01'))
            ->setExpiresAt(new \DateTimeImmutable('2026-01-10'));
    }

    private function activeBan(User $user): UserBan
    {
        return (new UserBan())
            ->setUser($user)
            ->setBanReason(BanReason::SPAM)
            ->setCreatedAt(new \DateTimeImmutable('2026-01-30'))
            ->setExpiresAt(new \DateTimeImmutable('2026-02-10'));
    }

    private function entityManager(): EntityManagerInterface
    {
        return self::getContainer()->get(EntityManagerInterface::class);
    }

    private function persist(UserBan ...$bans): void
    {
        $em = $this->entityManager();
        foreach ($bans as $ban) {
            $em->persist($ban);
        }
        $em->flush();
        $em->clear();
    }

    private function runCloseBanCommand(): void
    {
        $command = $this->application->find('app:moderation:close');
        (new CommandTester($command))->execute([]);
    }

    private function reload(UserBan $ban): UserBan
    {
        return $this->entityManager()->getRepository(UserBan::class)->find($ban->getId());
    }

    private function assertBanClosedByExpiration(UserBan $ban): void
    {
        $reloaded = $this->reload($ban);
        $this->assertSame(
            $reloaded->getExpiresAt()?->getTimestamp(),
            $reloaded->getExpiresAt()?->getTimestamp()
        );
    }

    private function assertBanStillActive(UserBan $ban): void
    {
        $reloaded = $this->reload($ban);
        $this->assertNull($reloaded->getEndedAt());
    }
}
