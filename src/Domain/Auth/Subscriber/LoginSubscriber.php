<?php

namespace App\Domain\Auth\Subscriber;

use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Event\BadPasswordLoginEvent;
use App\Domain\Auth\Service\LoginAttemptsService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

readonly class LoginSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private LoginAttemptsService $loginAttemptsService,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            BadPasswordLoginEvent::class => 'onLoginFailure',
            LoginSuccessEvent::class => 'onLoginSuccess',
        ];
    }

    /**
     * We increment the attempt login.
     */
    public function onLoginFailure(BadPasswordLoginEvent $event): void
    {
        $this->loginAttemptsService->increment($event->getUser());
    }

    /**
     * We update the lastLogin IP and lastLoginDatetime.
     */
    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();

        if (!$user instanceof User) {
            return;
        }
        $ip = $event->getRequest()->getClientIp();

        // We call the service to populate in the database.
        $this->loginAttemptsService->onLoginSuccess($user, $ip);
    }
}
