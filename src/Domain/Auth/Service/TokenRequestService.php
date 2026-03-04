<?php

namespace App\Domain\Auth\Service;

use App\Domain\Auth\Contract\TokenRequestRepositoryInterface;
use App\Domain\Auth\DTO\IssuedTokenRequestDTO;
use App\Domain\Auth\Entity\TokenRequest;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Enum\TokenRequestType;
use App\Domain\Auth\Exception\InvalidTokenException;
use App\Foundation\Security\TokenIssuer;
use Random\RandomException;

/**
 * TokenRequest Service.
 *
 * This service manages short-lived authentication tokens used for actions such as:
 *  - Email confirmation
 *  - Password reset
 *  - Magic login links
 *
 * Design goals:
 *  - Tokens are single-use
 *  - Tokens expire automatically
 *  - Only ONE active token can exist per (user + token type)
 *  - Raw tokens are never stored in the database
 *
 * Security model:
 *  - A secure random token is generated via TokenIssuer.
 *  - The raw token is sent to the user (email link, etc).
 *  - Only the hashed version of the token is stored in database.
 *  - When consuming the token, the hash is matched against stored values.
 *
 * Token lifecycle:
 *
 *   issue()
 *     ↓
 *   revoke existing active tokens
 *     ↓
 *   persist new TokenRequest
 *     ↓
 *   user receives raw token
 *     ↓
 *   consume()
 *     ↓
 *   token marked as consumed
 *
 * This ensures:
 *  - tokens cannot be reused
 *  - tokens cannot be guessed from database leaks
 *  - only one active token exists per user/type
 */
readonly class TokenRequestService
{
    public function __construct(
        private TokenRequestRepositoryInterface $tokenRequestRepository,
        private TokenIssuer $tokenIssuer,
    ) {
    }

    /**
     * Issue a new TokenRequest for a given user and type.
     *
     * This method guarantees that only ONE active token exists
     * per (user + TokenRequestType).
     *
     * Behavior:
     * 1. Revoke all currently consumable tokens for the same user and type
     *    (not consumed AND not expired).
     * 2. Generate a new secure token using TokenIssuer.
     * 3. Persist a new TokenRequest entity with:
     *      - hashed token
     *      - creation timestamp
     *      - expiration timestamp based on TokenRequestType TTL
     * 4. Return both the persisted TokenRequest and the raw token
     *    (used for email links).
     *
     * Important:
     * The raw token is never stored in database.
     * Only the hashed version is persisted for security reasons.
     *
     * @throws RandomException
     */
    public function issue(User $user, TokenRequestType $type, ?\DateTimeImmutable $now = null): IssuedTokenRequestDTO
    {
        $now ??= new \DateTimeImmutable();

        $this->tokenRequestRepository->revokeConsumableForUserAndType(
            userId: $user->getId(),
            type: $type,
            now: $now,
        );

        $issued = $this->tokenIssuer->issue();

        $tokenRequest = $this->createTokenRequest(
            user: $user,
            type: $type,
            hash: $issued->hash,
            now: $now,
        );

        $this->tokenRequestRepository->save($tokenRequest, true);

        return new IssuedTokenRequestDTO(
            request: $tokenRequest,
            issued: $issued,
        );
    }

    /**
     * Consume a TokenRequest using its hashed token.
     *
     * A token can only be consumed if it is:
     *  - not expired
     *  - not already consumed
     *
     * If the token is invalid, expired or already used,
     * an InvalidTokenException is thrown.
     *
     * On success:
     *  - consumedAt is set
     *  - the entity is persisted
     *
     * @throws InvalidTokenException
     */
    public function consume(
        string $hash,
        TokenRequestType $type,
        \DateTimeImmutable $now,
    ): TokenRequest {
        $request = $this->tokenRequestRepository->findConsumableByTokenHashAndType(
            tokenHash: $hash,
            type: $type,
            now: $now,
        );

        if (!$request) {
            throw new InvalidTokenException('Invalid token.');
        }

        $request->consume($now);
        $this->tokenRequestRepository->save($request, true);

        return $request;
    }

    private function createTokenRequest(
        User $user,
        TokenRequestType $type,
        string $hash,
        \DateTimeImmutable $now,
    ): TokenRequest {
        return (new TokenRequest())
            ->setUser($user)
            ->setType($type)
            ->setTokenHash($hash)
            ->setCreatedAt($now)
            ->setExpiresAt($now->add($type->ttl()));
    }
}
