<?php

namespace App\Domain\Auth\Subscriber;

use App\Domain\Auth\Event\BeforeUserRegisterEvent;
use App\Domain\Auth\Event\ConfirmationEmailRequestedEvent;
use App\Domain\Auth\Event\ConfirmationTokenIssuedEvent;
use App\Domain\Auth\Event\UserRegisteredEvent;
use App\Foundation\Mailing\MailerBuilder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Exception\ExceptionInterface;

readonly class AuthSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private MailerBuilder $mailerBuilder,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeUserRegisterEvent::class => 'onBeforeUserRegister',
            UserRegisteredEvent::class => 'onUserRegistered',
            ConfirmationTokenIssuedEvent::class => 'onConfirmationTokenIssued',
            ConfirmationEmailRequestedEvent::class => 'onConfirmationEmailRequested',
        ];
    }

    public function onBeforeUserRegister(BeforeUserRegisterEvent $event): void
    {
        // no-op for now
    }

    public function onUserRegistered(UserRegisteredEvent $event): void
    {
        // no-op for now
        // good place later for audit/log/analytics
    }

    public function onConfirmationTokenIssued(ConfirmationTokenIssuedEvent $event): void
    {
        // no-op for now
        // good place later for audit/log/token tracing
    }

    /**
     * @throws ExceptionInterface
     */
    public function onConfirmationEmailRequested(ConfirmationEmailRequestedEvent $event): void
    {
        $dto = $event->getIssuedTokenRequest();
        $user = $event->getUser();
        $token = $dto->getToken();

        $email = $this->mailerBuilder->buildEmail('mails/auth/register.twig', [
            'user' => $user,
            'token' => $token,
            'reason' => $event->getReason()->value,
        ])
            ->to($user->getEmail())
            ->subject('Laka Mark - Confirm your registration');

        $this->mailerBuilder->deliveryEmail($email);
    }
}
