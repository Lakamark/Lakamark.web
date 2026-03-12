<?php

namespace App\Domain\Auth\Service;

use App\Domain\Auth\DTO\ConfirmUserResultDTO;
use App\Domain\Auth\Enum\TokenRequestType;
use App\Domain\Auth\Enum\UserRole;
use App\Domain\Auth\Exception\TokenRequest\InvalidTokenException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockInterface;

/**
 * Confirms a user account from a valid confirmation token.
 *
 * This service supports both the initial registration confirmation flow
 * and the resend confirmation email flow.
 *
 * Allowed token types:
 * - REGISTER_CONFIRMATION
 * - EMAIL_CONFIRMATION
 *
 * When a valid token is consumed, the service:
 * - marks the user email as confirmed
 * - grants the verified user role if missing
 * - persists the changes
 */
readonly class ConfirmAccountService
{
    /**
     * Token types allowed for account confirmation.
     */
    private const array ALLOWED_TOKEN_TYPES = [
        TokenRequestType::REGISTER_CONFIRMATION,
        TokenRequestType::EMAIL_CONFIRMATION,
    ];

    public function __construct(
        private TokenRequestService $tokenRequestService,
        private UserRoleManagerService $roleManagerService,
        private EntityManagerInterface $em,
        private ClockInterface $clock,
    ) {
    }

    /**
     * Confirms the user account associated with the given raw token.
     *
     * The token is resolved against the supported confirmation token types.
     * If the token is valid, the user email is confirmed and the verified
     * role is granted when needed.
     *
     * @param string $rawToken raw token received from the confirmation link
     *
     * @return ConfirmUserResultDTO Result describing the confirmation outcome.
     *
     * If the token does not match any allowed confirmation token type.
     *
     * @throws InvalidTokenException
     */
    public function confirm(string $rawToken): ConfirmUserResultDTO
    {
        $tokenRequest = $this->tokenRequestService->consumeAnyOfTypes(
            rawToken: $rawToken,
            types: self::ALLOWED_TOKEN_TYPES
        );

        $user = $tokenRequest->getUser();
        $now = $this->clock->now();

        $emailConfirmed = false;
        $roleVerifiedAdded = false;

        if (!$user->isEmailConfirmed()) {
            $user->confirmEmail($now);
            $emailConfirmed = true;
        }

        if (!$this->roleManagerService->has($user, UserRole::USER_VERIFIED)) {
            $this->roleManagerService->grant($user, UserRole::USER_VERIFIED);
            $roleVerifiedAdded = true;
        }

        $this->em->flush();

        return new ConfirmUserResultDTO(
            user: $user,
            emailConfirmed: $emailConfirmed,
            roleVerifiedAdded: $roleVerifiedAdded,
        );
    }
}
