<?php

namespace App\Domain\Moderation\Subscriber;

use App\Domain\Moderation\Event\BanUserEvent;
use App\Domain\Moderation\Event\UnbanUserEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ModerationSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            BanUserEvent::class => 'onBanUserEvent',
            UnbanUserEvent::class => 'onUnbanUserEvent',
        ];
    }

    public function onBanUserEvent(BanUserEvent $event): void
    {
    }

    public function onUnbanUserEvent(UnbanUserEvent $event): void
    {
    }
}
