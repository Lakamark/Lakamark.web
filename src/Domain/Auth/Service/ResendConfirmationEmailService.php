<?php

namespace App\Domain\Auth\Service;

use App\Domain\Auth\DTO\ResendConfirmationResultDTO;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Enum\TokenRequestType;
use App\Domain\Auth\Event\UserResentConfirmationEvent;
use App\Domain\Auth\Repository\UserRepository;
use Psr\EventDispatcher\EventDispatcherInterface;
use Random\RandomException;

/**
 * Handles the resend confirmation email workflow.
 *
 * This service allows an existing user with an unconfirmed email address
 * to request a new confirmation link.
 *
 * The workflow is:
 * - find the user by email
 * - ensure the account is not already confirmed
 * - issue a new confirmation token
 * - dispatch the resend confirmation event
 *
 * The returned DTO describes the outcome of the operation so the caller
 * can decide how to respond.
 */
readonly class ResendConfirmationEmailService
{
    public function __construct(
        private UserRepository $userRepository,
        private TokenRequestService $tokenRequestService,
        private EventDispatcherInterface $dispatcher,
    ) {
    }

    /**
     * Sends a new confirmation email for an unverified user account.
     *
     * If the user exists and their email is not yet confirmed,
     * a new confirmation token is generated and the appropriate
     * events are dispatched to send the confirmation email.
     *
     * @param string $email the user email used to locate the account
     *
     * @return ResendConfirmationResultDTO Result describing the resend operation.
     *
     * Possible outcomes:
     *  - success: a new confirmation email has been issued
     *  - userNotFound: no user exists with this email
     *  - alreadyConfirmed: the user email is already confirmed
     *
     * @throws RandomException if token generation fails
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

        $issuedTokenRequest = $this->tokenRequestService->issue(
            user: $user,
            type: TokenRequestType::EMAIL_CONFIRMATION,
        );

        $this->dispatcher->dispatch(
            new UserResentConfirmationEvent($issuedTokenRequest)
        );

        return new ResendConfirmationResultDTO(
            success: true,
            user: $user,
        );
    }
}
