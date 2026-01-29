<?php

namespace App\Domain\Auth\Security;

use App\Domain\Auth\Entity\User;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Before to log in the user, we will check in the BannedUser table,
 * If the current user has banned record is enabled or not.
 */
class BannedUserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        // Add some code later...
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // To ensure the parameter is instance of User entity.
        if (!$user instanceof User) {
            return;
        }
        // TODO: Implement checkPostAuth() method.
    }
}
