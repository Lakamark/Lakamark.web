<?php

namespace App\Domain\Auth\Security;

use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Exception\BannedUserException;
use App\Domain\Moderation\Repository\UserBanRepository;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class BannedUserChecker implements UserCheckerInterface
{
    public function __construct(
        private UserBanRepository $repository,
    ) {
    }

    public function checkPreAuth(UserInterface $user): void
    {
    }

    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        $ban = $this->repository->findActiveBanFor($user, new \DateTimeImmutable());
        if (null !== $ban) {
            throw new BannedUserException('Invalid credentials.');
        }
    }
}
