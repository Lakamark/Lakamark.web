<?php

namespace App\Domain\Auth\Subscriber;

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
            UserRegisteredEvent::class => 'onUserRegistered',
        ];
    }

    /**
     * @throws ExceptionInterface
     */
    public function onUserRegistered(UserRegisteredEvent $event): void
    {
        // If the user use oauth login e.g. (Facebook, Google GitHub etc.)
        if ($event->isUseOauthRequest()) {
            return;
        }

        // Get the requestToken
        $requestTokenDto = $event->getIssuedTokenRequestDto();

        // user
        $user = $requestTokenDto->request->getUser();

        // hash
        $hash = $requestTokenDto->request->getTokenHash();

        // Create a confirmation email and send it.
        $email = $this->mailerBuilder->buildEmail('mails/auth/register.twig', [
            'user' => $user,
            'hash' => $hash,
        ])
            ->to($user->getEmail())
            ->subject('Laka Mark - Confirm your registration');

        // send in the queue.
        $this->mailerBuilder->deliveryEmail($email);
    }
}
