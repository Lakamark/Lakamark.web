<?php

namespace App\Domain\Auth\Security;

use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Exception\BannedUserException;
use App\Domain\Moderation\Service\ModerationService;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Before to log in the user, we will check in the BannedUser table,
 * If the current user has banned record is enabled or not.
 */
readonly class BannedUserChecker implements UserCheckerInterface
{
    public function __construct(
        private ModerationService $moderationService,
        private ClockInterface $clock,
    ) {
    }

    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        $now = $this->clock->now();
        if ($this->moderationService->isUserBanned($user, $now)) {
            throw new BannedUserException();
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // Do nothing for now.
    }
}
