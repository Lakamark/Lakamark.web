<?php

namespace App\Domain\Moderation\Service;

use App\Domain\Auth\Entity\User;
use App\Domain\Moderation\Entity\UserBan;
use App\Domain\Moderation\Enum\BanReason;
use App\Domain\Moderation\Event\BannedUserEvent;
use App\Domain\Moderation\Event\UnbannedUserEvent;
use App\Domain\Moderation\Exception\CannotUnbanBotUserException;
use App\Domain\Moderation\Repository\UserBanRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

final readonly class ModerationService
{
    public function __construct(
        private EntityManagerInterface $em,
        private EventDispatcherInterface $dispatcher,
        private UserBanRepository $userBanRepository,
    ) {
    }

    /**
     * Ban a user if not already actively banned.
     *
     * Rules:
     * - Allowed for all users (including BOT)
     * - No-op if an active ban already exists
     *
     * @throws \InvalidArgumentException When expiresAt is in the past (or equals now)
     */
    public function banUser(
        User $user,
        BanReason $reason,
        \DateTimeImmutable $now,
        ?\DateTimeImmutable $expiresAt = null,
        ?string $details = null,
    ): void {
        // no-op if already banned
        if (null !== $this->userBanRepository->findActiveBanFor($user, $now)) {
            return;
        }

        // Optional guard: prevents "instant-expired" bans
        if (null !== $expiresAt && $expiresAt <= $now) {
            throw new \InvalidArgumentException('expiresAt must be in the future.');
        }

        $ban = (new UserBan())
            ->setUser($user)
            ->setBanReason($reason)
            ->setDetails($details)
            ->setCreatedAt($now)
            ->setExpiresAt($expiresAt);

        $this->em->persist($ban);
        $this->em->flush();

        $this->dispatcher->dispatch(new BannedUserEvent($user, $ban));
    }

    /**
     * Unban a user (end the active ban) unless the active ban is a BOT ban.
     *
     * Rules:
     * - No-op if user has no active ban
     * - Forbidden if the active ban reason is BOT
     *
     * @throws CannotUnbanBotUserException
     */
    public function unbanUser(User $user, \DateTimeImmutable $now): void
    {
        $ban = $this->userBanRepository->findActiveBanFor($user, $now);
        if (!$ban) {
            return;
        }

        // Business rule: do not allow unbanning BOT bans
        if (BanReason::BOT === $ban->getBanReason()) {
            throw new CannotUnbanBotUserException();
        }

        $ban->endManually($now);

        // No need to persist: Doctrine is already tracking the entity
        $this->em->flush();

        $this->dispatcher->dispatch(new UnbannedUserEvent($user, $now));
    }
}
