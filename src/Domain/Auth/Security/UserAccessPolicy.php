<?php

declare(strict_types=1);

namespace App\Domain\Auth\Security;

use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Enum\UserAccess;
use App\Domain\Moderation\Service\ModerationService;
use Symfony\Component\Clock\ClockInterface;

readonly class UserAccessPolicy
{
    public function __construct(
        private ModerationService $moderation,
        private ClockInterface $clock,
    ) {
    }

    public function has(User $user, UserAccess $userAccess): bool
    {
        $now = $this->clock->now();

        return match ($userAccess) {
            UserAccess::VERIFIED => $user->isEmailConfirmed(),
            UserAccess::BANNED => $this->moderation->isUserBanned($user, $now),
        };
    }
}
