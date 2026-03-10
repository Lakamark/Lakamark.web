<?php

namespace App\Domain\Auth\Subscriber;

use App\Domain\Auth\Event\BeforeUserRegisterEvent;
use App\Domain\Auth\Event\UserRegisteredEvent;
use App\Domain\Auth\Event\UserResentConfirmationEvent;
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
            UserResentConfirmationEvent::class => 'onUserResentConfirmation',
        ];
    }

    public function onBeforeUserRegister(BeforeUserRegisterEvent $event): void
    {
        // no-op for now
    }

    /**
     * @throws ExceptionInterface
     */
    public function onUserRegistered(UserRegisteredEvent $event): void
    {
        $dto = $event->getIssuedTokenRequestDto();
        $user = $dto->getUser();
        $token = $dto->getToken();

        $email = $this->mailerBuilder->buildEmail('mails/auth/register.twig', [
            'user' => $user,
            'token' => $token,
        ])
            ->to($user->getEmail())
            ->subject('Laka Mark - Confirm your registration');

        $this->mailerBuilder->deliveryEmail($email);
    }

    public function onUserResentConfirmation(UserResentConfirmationEvent $event): void
    {
    }
}
