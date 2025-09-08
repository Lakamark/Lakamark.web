<?php

namespace App\Domain\Auth\Subscriber;

use App\Domain\Auth\Event\BeforeUserCreatedEvent;
use App\Domain\Auth\Service\RegistrationDurationService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

readonly class RegistrationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RegistrationDurationService $registrationDurationService,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => 'onRequest',
            BeforeUserCreatedEvent::class => 'onRegister',
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        // The request is not register action
        if (
            'register' !== $event->getRequest()->attributes->get('_route')
            || !$event->getRequest()->isMethod('GET')
        ) {
            return;
        }

        $this->registrationDurationService->startTimer($event->getRequest());
    }

    public function onRegister(RequestEvent $event): void
    {
        $this->registrationDurationService->startTimer($event->getRequest());
    }
}
