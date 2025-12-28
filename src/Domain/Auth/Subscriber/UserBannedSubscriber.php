<?php

namespace App\Domain\Auth\Subscriber;

use App\Domain\Auth\Event\UserBannedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

readonly class UserBannedSubscriber implements EventSubscriberInterface
{
    public function __construct()
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserBannedEvent::class => 'onUserBanned',
        ];
    }

    public function onUserBanned(UserBannedEvent $event): void
    {
        // Add the logic notify, revoke api token etc.
    }
}
