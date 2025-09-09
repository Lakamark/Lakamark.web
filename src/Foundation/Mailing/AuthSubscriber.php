<?php

namespace App\Foundation\Mailing;

use App\Domain\Auth\Event\UserRegisteredEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

readonly class AuthSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private MailerBuilder $mailerBuilder,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserRegisteredEvent::class => 'onUserRegistered',
        ];
    }

    public function onUserRegistered(UserRegisteredEvent $event): void
    {
        // If the user use oauth login e.g. (Facebook, Google GitHub etc.)
        if ($event->useOauthRequest()) {
            return;
        }

        // Create a confirmation email and send it
        $email = $this->mailerBuilder->buildEmail('mails/auth/register.twig', [
            'user' => $event->getUser(),
        ])
            ->to($event->getUser()->getEmail())
            ->subject('Lakamark.com - account confirmation');
        $this->mailerBuilder->deliveryEmail($email);
    }
}
