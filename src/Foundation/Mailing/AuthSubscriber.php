<?php

namespace App\Foundation\Mailing;

use App\Domain\Auth\Event\UserCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AuthSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly MailerBuilder $mailerBuilder,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserCreatedEvent::class => 'onRegisteredUser',
        ];
    }

    public function onRegisteredUser(UserCreatedEvent $event): void
    {
        if ($event->isConnectedWithOauth()) {
            return;
        }

        $email = $this->mailerBuilder->buildEmail('mailers/auth/register.twig', [
            'user' => $event->getUser(),
        ])
            ->to($event->getUser()->getEmail())
            ->subject('Welcome to lakamark.com - Please confirm your email address.');
        $this->mailerBuilder->sendNow($email);
    }
}
