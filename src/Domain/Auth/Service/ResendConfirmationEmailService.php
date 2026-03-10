<?php

namespace App\Domain\Auth\Service;

use App\Domain\Auth\DTO\ResendConfirmationResultDTO;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Enum\OAuthProvider;
use App\Domain\Auth\Enum\TokenRequestType;
use App\Domain\Auth\Event\UserRegisteredEvent;
use App\Domain\Auth\Repository\UserRepository;
use Psr\EventDispatcher\EventDispatcherInterface;
use Random\RandomException;

readonly class ResendConfirmationEmailService
{
    public function __construct(
        private UserRepository $userRepository,
        private TokenRequestService $tokenRequestService,
        private EventDispatcherInterface $dispatcher,
    ) {
    }

    /**
     * @throws RandomException
     */
    public function resend(
        string $email,
    ): ResendConfirmationResultDTO {
        $user = $this->userRepository->findByUsernameIdentifier($email);

        if (!$user instanceof User) {
            return new ResendConfirmationResultDTO(
                success: false,
                userNotFound: true,
            );
        }

        if (null !== $user->getConfirmAt()) {
            return new ResendConfirmationResultDTO(
                success: false,
                user: $user,
                alreadyConfirmed: true,
            );
        }

        $this->tokenRequestService->issue(
            user: $user,
            type: TokenRequestType::REGISTER_CONFIRMATION,
        );

        $this->dispatcher->dispatch(
            new UserRegisteredEvent(
                user: $user,
                authProvider: OAuthProvider::LOCAL,
            )
        );

        return new ResendConfirmationResultDTO(
            success: true,
            user: $user,
        );
    }
}
