<?php

namespace App\Domain\Auth\Security;

use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Exception\LockedUserException;
use App\Domain\Auth\Exception\TooManyFailedAttemptsException;
use App\Domain\Auth\Exception\UserNotFoundException;
use App\Domain\Auth\Service\LoginAttemptsService;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function __construct(
        private readonly LoginAttemptsService $loginAttemptsService,
    ) {
    }

    public function checkPreAuth(UserInterface $user): void
    {
        if ($user instanceof User && $this->loginAttemptsService->reachedAttemptFor($user)) {
            throw new TooManyFailedAttemptsException();
        }

        return;
    }

    public function checkPostAuth(UserInterface $user): void
    {
        if ($user instanceof User && $user->isLocked()) {
            throw new LockedUserException();
        }
        if ($user instanceof User && null !== $user->getConfirmAt()) {
            throw new UserNotFoundException();
        }

        return;
    }
}
