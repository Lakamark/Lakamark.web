<?php

namespace App\Domain\Auth\Service;

use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Event\UserBannedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

readonly class UserBanService
{
    public function __construct(
        private EntityManagerInterface $em,
        private EventDispatcherInterface $dispatcher,
    ) {
    }

    public function ban(User $user): void
    {
        if ($user->isLocked()) {
            return;
        }

        $user->setLockedAt(new \DateTimeImmutable());
        $this->em->persist($user);
        $this->em->flush();

        $this->dispatcher->dispatch(new UserBannedEvent($user));
    }
}
