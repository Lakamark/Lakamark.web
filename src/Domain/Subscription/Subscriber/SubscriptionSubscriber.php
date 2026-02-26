<?php

namespace App\Domain\Subscription\Subscriber;

use App\Domain\Subscription\Event\ActivateSubscriptionEvent;
use App\Domain\Subscription\Event\CanceledSubscriptionEvent;
use App\Domain\Subscription\Event\Patreon\ActivatePatronSubscriptionEvent;
use App\Domain\Subscription\Event\Patreon\CancelPatronSubscriptionEvent;
use App\Domain\Subscription\Service\SubscriptionService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class SubscriptionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private SubscriptionService $service,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ActivatePatronSubscriptionEvent::class => 'onPatreonActivated',
            CancelPatronSubscriptionEvent::class => 'onPatreonCanceled',
            ActivateSubscriptionEvent::class => 'onManualActivated',
            CanceledSubscriptionEvent::class => 'onManualCanceled',
        ];
    }

    public function onManualActivated(ActivateSubscriptionEvent $event): void
    {
        $this->service->activateManual(
            $event->getUserId(),
            $event->getPeriodEnd()
        );
    }

    public function onManualCanceled(CanceledSubscriptionEvent $event): void
    {
        $this->service->cancelManual($event->getUserId(), $event->getPeriodEnd());
    }

    public function onPatreonActivated(ActivatePatronSubscriptionEvent $event): void
    {
        $this->service->activateFromPatreon(
            $event->getUserId(),
            $event->getPatreonId(),
            $event->getPeriodEnd());
    }

    public function onPatreonCanceled(CancelPatronSubscriptionEvent $event): void
    {
        $this->service->cancelFromPatreon(
            $event->getUserId(),
            $event->getPatreonId(),
            $event->getPeriodEnd()
        );
    }
}
