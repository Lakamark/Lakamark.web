<?php

namespace App\Domain\Auth\Service;

use App\Domain\Auth\Entity\LoginAttempt;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Repository\LoginAttemptRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Tracks failed login attempts and determines when a user has reached
 * the maximum allowed number of attempts.
 */
class LoginAttemptsService
{
    public const int MAX_LOGIN_ATTEMPTS = 3;

    public function __construct(
        private readonly LoginAttemptRepository $loginAttemptRepository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * Each time the user fail to be authenticated.
     * We store attempts number in the database.
     *
     * If the user successfully login we reset the attempts, or
     * we won't increment the attempts tries if the user successful on first try.
     */
    public function increment(User $user): void
    {
        $userAttempt = (new LoginAttempt())->setUser($user);
        $this->em->persist($userAttempt);
        $this->em->flush();
    }

    public function onLoginSuccess(User $user, ?string $ip): void
    {
        // reset attempt
        $this->loginAttemptRepository->resetAttemptsFor($user);

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
    public function hasReachedAttemptFor(User $user): bool
    {
        $attempts = $this->loginAttemptRepository->countRecentAttemptsFor($user, minutes: 30);

        return $attempts >= self::MAX_LOGIN_ATTEMPTS;
    }
}
