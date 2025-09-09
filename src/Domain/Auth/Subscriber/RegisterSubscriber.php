<?php

namespace App\Domain\Auth\Subscriber;

use App\Domain\Auth\Event\BeforeUserRegisterEvent;
use App\Domain\Auth\Service\RegistrationDurationService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

readonly class RegisterSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RegistrationDurationService $registrationDurationService,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => 'onRequest',
            BeforeUserRegisterEvent::class => 'onBeforeUserRegister',
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        /*
         * If the current request is not register request, will do nothing.
         * Because this event is lunched on all request.
         */
        if (
            'register' !== $event->getRequest()->attributes->get('_route')
            || !$event->getRequest()->isMethod('GET')
        ) {
            return;
        }

        // Otherwise, we start a timer
        $this->registrationDurationService->startTimer($event->getRequest());
    }

    public function onBeforeUserRegister(BeforeUserRegisterEvent $event): void
    {
        $event->user->setRegisterTimerDuration($this->registrationDurationService->getDuration($event->request));
    }
}
