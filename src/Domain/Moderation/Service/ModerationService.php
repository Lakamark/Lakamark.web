<?php

namespace App\Domain\Moderation\Service;

use App\Domain\Auth\Entity\User;
use App\Domain\Moderation\Entity\UserBan;
use App\Domain\Moderation\Enum\BanReason;
use App\Domain\Moderation\Event\BannedUserEvent;
use App\Domain\Moderation\Event\UnbannedUserEvent;
use App\Domain\Moderation\Exception\InvalidDateArgumentException;
use App\Domain\Moderation\Repository\UserBanRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

readonly class ModerationService
{
    public function __construct(
        private UserBanRepository $userBanRepository,
        private EntityManagerInterface $em,
        private EventDispatcherInterface $dispatcher,
    ) {
    }

    public function banUser(
        User $user,
        BanReason $reason,
        \DateTimeImmutable $now,
        ?\DateTimeImmutable $expiresAt = null,
        ?string $details = null,
    ): void {
        $queryActiveBan = $this->userBanRepository->findActiveBanFor($user, $now);

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

        // We persist the new band record in the database.
        $this->em->persist($newBan);
        $this->em->flush();

        // Dispatch the event
        $this->dispatcher->dispatch(new BannedUserEvent($user, $now));
    }

    public function unbanUser(
        User $user,
        ?\DateTimeImmutable $now = null,
    ): void {
        $now ??= new \DateTimeImmutable();
        $ban = $this->userBanRepository->findActiveBanFor($user, $now);

        if (null === $ban) {
            return;
        }

        $ban->endManually($now);

        $this->em->flush();

        $this->dispatcher->dispatch(new UnbannedUserEvent($user, $ban));
    }
}
