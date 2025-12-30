<?php

namespace App\Domain\Auth\Subscriber;

use App\Domain\Moderation\Event\UserBannedEvent;
use App\Domain\Moderation\Event\UserUnbannedEvent;
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
            UserUnbannedEvent::class => 'onUserUnBanned',
        ];
    }

    public function onUserBanned(UserBannedEvent $event): void
    {
    }

    public function onUserUnBanned(UserUnbannedEvent $event): void
    {
    }
}
