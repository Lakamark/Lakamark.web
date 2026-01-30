<?php

namespace App\Domain\Auth\Security;

use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Exception\TooManyLoginAttemptsException;
use App\Domain\Auth\Service\LoginAttemptsService;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

readonly class TooManyLoginAttemptsUserChecker implements UserCheckerInterface
{
    public function __construct(
        private LoginAttemptsService $loginAttemptsService,
    ) {
    }

    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        // Check if user has to many failed tries.
        if ($this->loginAttemptsService->hasReachedAttemptFor($user)) {
            throw new TooManyLoginAttemptsException();
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
    }
}
