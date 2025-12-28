<?php

namespace App\Domain\Auth\Service;

use App\Domain\Auth\Entity\AuthAttempt;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Repository\AuthAttemptRepository;
use Doctrine\ORM\EntityManagerInterface;

class LoginAttemptsService
{
    final public const int LOGIN_ATTEMPTS = 3;

    public function __construct(
        private readonly AuthAttemptRepository $authAttemptRepository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function incrementAttempt(User $user): void
    {
        $getUserAttempt = (new AuthAttempt())->setUser($user);
        $this->em->persist($getUserAttempt);
        $this->em->flush();
    }

    public function reachedAttemptFor(User $user): bool
    {
        return $this->authAttemptRepository->incrementAttemptsFor($user, 30) >= self::LOGIN_ATTEMPTS;
    }
}
