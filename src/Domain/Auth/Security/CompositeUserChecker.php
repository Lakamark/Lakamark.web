<?php

namespace App\Domain\Auth\Security;

use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class CompositeUserChecker implements UserCheckerInterface
{
    /** @var iterable<UserCheckerInterface> */
    private iterable $checkers;

    /**
     * @param iterable<UserCheckerInterface> $checkers
     */
    public function __construct(iterable $checkers)
    {
        $this->checkers = $checkers;
    }

    public function checkPreAuth(UserInterface $user): void
    {
        foreach ($this->checkers as $checker) {
            $checker->checkPreAuth($user);
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        foreach ($this->checkers as $checker) {
            $checker->checkPostAuth($user);
        }
    }
}
