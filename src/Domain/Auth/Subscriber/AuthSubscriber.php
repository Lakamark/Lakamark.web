<?php

namespace App\Domain\Auth\Subscriber;

use App\Domain\Auth\Contract\ConfirmationTokenEventInterface;
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
            UserRegisteredEvent::class => 'onConfirmationRequested',
            UserResentConfirmationEvent::class => 'onConfirmationRequested',
        ];
    }

    /**
     * @throws ExceptionInterface
     */
    public function onConfirmationRequested(
        ConfirmationTokenEventInterface $event,
    ): void {
        if ($event instanceof UserRegisteredEvent && $event->isUseOauthRequest()) {
            return;
        }

        $dto = $event->getIssuedTokenRequestDto();
        $user = $dto->request->getUser();
        $token = $dto->getToken();

        $this->sendEmail($user, $token);
    }

    /**
     * @throws ExceptionInterface
     */
    private function sendEmail(User $user, string $token): void
    {
        $email = $this->mailerBuilder->buildEmail('mails/auth/register.twig', [
            'user' => $user,
            'token' => $token,
        ])
            ->to($user->getEmail())
            ->subject('Laka Mark - Confirm your registration');

        $this->mailerBuilder->deliveryEmail($email);
    }
}
