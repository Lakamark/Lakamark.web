<?php

namespace App\Domain\Auth\Service;

use App\Domain\Auth\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Tracks failed login attempts and determines when a user has reached
 * the maximum allowed number of attempts.
 */
class LoginAttemptsService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    public const int MAX_LOGIN_ATTEMPTS = 3;

    /**
     * Each time the user fail to be authenticated.
     * We store attempts number in the database.
     *
     * If the user successfully login we reset the attempts, or
     * we won't increment the attempts tries if the user successful on first try.
     */
    public function increment(User $user): void
    {
        // TODO: Implement onLoginFailure() method.
    }

    public function onLoginSuccess(User $user, ?string $ip): void
    {
        // reset attempt
        // TODO: Create the LoginAttempts entity with the repository!

        // Update audit fields
        if (null !== $ip && $ip !== $user->getLastLoginIp()) {
            $user->setLastLoginIp($ip);
        }

        $user->setLastLoginAt(new \DateTimeImmutable());

        // save in the entity updated audit fields.
        $this->em->persist($user);
        $this->em->flush();
    }

    /**
     * Make a request in the loginAttempts table the current attempts
     * for the current user.
     * We compare the current attempts for a specific user with the const
     * MAX_LOGIN_ATTEMPTS defined in this service.
     */
    public function hasReachedAttemptFor(UserInterface $user): bool
    {
        if (!$user instanceof User) {
            return false;
        }

        // TODO query loginAttempts repository
        return false;
    }
}
