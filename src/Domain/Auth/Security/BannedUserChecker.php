<?php

namespace App\Domain\Auth\Security;

use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Enum\UserAccess;
use App\Domain\Auth\Exception\BannedUserException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Before logging in the user, check whether an active ban exists.
 */
readonly class BannedUserChecker implements UserCheckerInterface
{
    public function __construct(
        private UserAccessPolicy $policy,
    ) {
    }

    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if ($this->policy->has($user, UserAccess::BANNED)) {
            throw new BannedUserException();
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // Do nothing for now.
    }
}
