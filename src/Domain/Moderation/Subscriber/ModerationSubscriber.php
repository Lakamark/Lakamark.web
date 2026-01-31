<?php

namespace App\Domain\Moderation\Subscriber;

use App\Domain\Moderation\Event\BannedUserEvent;
use App\Domain\Moderation\Event\UnbannedUserEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ModerationSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            BannedUserEvent::class => 'onUserBanned',
            UnbannedUserEvent::class => 'onUserUnbanned',
        ];
    }

    public function onUserBanned(BannedUserEvent $event): void
    {
    }

    public function onUserUnbanned(UnbannedUserEvent $event): void
    {
    }
}
