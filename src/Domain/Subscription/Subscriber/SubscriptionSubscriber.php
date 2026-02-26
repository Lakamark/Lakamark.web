<?php

namespace App\Domain\Subscription\Subscriber;

use App\Domain\Subscription\Repository\SubscriptionRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class SubscriptionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private SubscriptionRepository $subscriptionRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // ActivatePatronSubscriptionEvent::class => 'onPatreonActivated',
            // CancelPatronSubscriptionEvent::class   => 'onPatreonCanceled',
            // ActivateSubscriptionEvent::class       => 'onManualActivated',
            // CanceledSubscriptionEvent::class       => 'onManualCanceled',
        ];
    }

    // public function onPatreonActivated(ActivatePatronSubscriptionEvent $event): void
    // {
    //     $this->subscriptions->activateFromPatreon($event->userId, $event->patronId, $event->periodEnd);
    // }
}
