<?php

namespace App\Domain\Badge\Service;

use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class BadgeMangerService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /**
     * Unlock a badge if the user meet the required attempts action count.
     * If the user successfully meet the actionName and the action cont we unlock the badge.
     */
    public function checkAndUnlock(int $userId, string $actionName, int $actionCount): void
    {
        // Check if the badge exist for the action name and action count.
        // Then we check if the user has already this badge
        // Then unlock the badge for the current user
        // Dispatch an event to notify the application subscriber about the unluck event
    }
}
