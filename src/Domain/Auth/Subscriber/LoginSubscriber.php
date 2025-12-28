<?php

namespace App\Domain\Auth\Subscriber;

use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Event\BadPasswordLoginEvent;
use App\Domain\Auth\Service\LoginAttemptsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

readonly class LoginSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private LoginAttemptsService $loginAttemptsService,
        private EntityManagerInterface $em,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            BadPasswordLoginEvent::class => 'onAuthenticationFailure',
            LoginSuccessEvent::class => 'onAuthentication',
        ];
    }

    /**
     * We increment the attempt login.
     */
    public function onAuthenticationFailure(BadPasswordLoginEvent $event): void
    {
        $this->loginAttemptsService->incrementAttempt($event->getUser());
    }

    public function onAuthentication(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        $event->getRequest()->getClientIp();

        // We check if the IP address is different
        // between the IP request and the last storage IP address in the user table
        // We update fields (loginIp) and (lastLoginAt) in the user table.
        if ($user instanceof User) {
            $ip = $event->getRequest()->getClientIp();

            if ($ip !== $user->getLastLoginIp()) {
                $user->setLastLoginIp($ip);
            }
            $user->setLastLoginAt(new \DateTimeImmutable());
            $this->em->persist($user);
        }
    }
}
