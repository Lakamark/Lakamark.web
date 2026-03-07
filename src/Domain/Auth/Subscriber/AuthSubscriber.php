<?php

namespace App\Domain\Auth\Subscriber;

use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Event\UserRegisteredEvent;
use App\Domain\Auth\Event\UserResentConfirmationEvent;
use App\Foundation\Mailing\MailerBuilder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Exception\ExceptionInterface;

/**
 * FIXME: handle unconfirmed users trying to login
 * allow resend confirmation token.
 */
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
            UserResentConfirmationEvent::class => 'onUserResentConfirmation',
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
        $this->buildEmailAndSend($user, $hash);
    }

    /**
     * @throws ExceptionInterface
     */
    public function onUserResentConfirmation(UserResentConfirmationEvent $event): void
    {
        // Get the requestToken
        $requestTokenDto = $event->getTokenRequestDTO();

        // user
        $user = $requestTokenDto->request->getUser();

        // hash
        $hash = $requestTokenDto->request->getTokenHash();
        $this->buildEmailAndSend($user, $hash);
    }

    /**
     * @throws ExceptionInterface
     */
    private function buildEmailAndSend(User $user, string $hash): void
    {
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
