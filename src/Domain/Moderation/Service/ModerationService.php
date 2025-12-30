<?php

namespace App\Domain\Moderation\Service;

use App\Domain\Auth\Entity\User;
use App\Domain\Moderation\Entity\UserBan;
use App\Domain\Moderation\Enum\BanReasonEnum;
use App\Domain\Moderation\Event\BanUserEvent;
use App\Domain\Moderation\Event\UnbanUserEvent;
use App\Domain\Moderation\Exception\InvalidDateArgumentException;
use App\Domain\Moderation\Repository\UserBanRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

readonly class ModerationService
{
    public function __construct(
        private EntityManagerInterface $em,
        private EventDispatcherInterface $dispatcher,
        private UserBanRepository $repository,
    ) {
    }

    /**
     * Ban an user.
     */
    public function banUser(
        User $user,
        BanReasonEnum $reason,
        \DateTimeImmutable $now,
        ?string $details = null,
        ?\DateTimeImmutable $expiresAt = null,
    ): void {
        $queryActiveBan = $this->repository->findActiveBanFor($user, $now);

        // If we fund an active ban we do nothing.
        if (null !== $queryActiveBan) {
            return;
        }

        // We can define an expires date in the past!
        if (null !== $expiresAt && $expiresAt <= $now) {
            throw new InvalidDateArgumentException('expiresAt must be in the future.');
        }

        // Create a ban record
        $newBan = (new UserBan())
            ->setUser($user)
            ->setBanReason($reason)
            ->setDetails($details)
            ->setCreatedAt($now)
            ->setExpiresAt($expiresAt)
            ->setEndedAt(null)
        ;

        // We persist the new ban records to the entityManager
        $this->em->persist($newBan);
        $this->em->flush();

        $this->dispatcher->dispatch(new BanUserEvent($user, $now));
    }

    /**
     * To unban a user.
     */
    public function unbanUser(User $user, ?\DateTimeImmutable $now = null): void
    {
        $now ??= new \DateTimeImmutable();
        $ban = $this->repository->findActiveBanFor($user, $now);

        if (null === $ban) {
            return;
        }

        $ban->endManually($now);

        $this->em->flush();

        $this->dispatcher->dispatch(new UnbanUserEvent($user, $ban));
    }
}
