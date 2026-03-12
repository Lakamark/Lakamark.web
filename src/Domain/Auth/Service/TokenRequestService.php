<?php

namespace App\Domain\Auth\Service;

use App\Domain\Auth\Contract\TokenRequestRepositoryInterface;
use App\Domain\Auth\DTO\IssuedTokenRequestDTO;
use App\Domain\Auth\Entity\TokenRequest;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Enum\TokenRequestType;
use App\Domain\Auth\Exception\TokenRequest\InvalidTokenException;
use App\Foundation\Security\TokenHasher;
use App\Foundation\Security\TokenIssuer;
use Random\RandomException;
use Symfony\Component\Clock\ClockInterface;

readonly class TokenRequestService
{
    public function __construct(
        private TokenRequestRepositoryInterface $tokenRequestRepository,
        private TokenIssuer $tokenIssuer,
        private TokenHasher $tokenHasher,
        private ClockInterface $clock,
    ) {
    }

    /**
     * @throws RandomException
     */
    public function issue(
        User $user,
        TokenRequestType $type,
    ): IssuedTokenRequestDTO {
        $now = $this->clock->now();

        $usableRequests = $this->tokenRequestRepository->findUsableForUserAndType(
            $user,
            $type,
            $now
        );

        // Revoke all previously the user token owner
        foreach ($usableRequests as $usableRequest) {
            $usableRequest->revoke($now);
        }

        $generated = $this->tokenIssuer->issue();

        $request = (new TokenRequest())
            ->setUser($user)
            ->setType($type)
            ->setTokenHash($generated->hash)
            ->setCreatedAt($now)
            ->setExpiresAt($now->add($type->ttl()));

        $this->tokenRequestRepository->save($request, true);

        return new IssuedTokenRequestDTO(
            request: $request,
            generated: $generated,
        );
    }

    public function consume(
        string $rawToken,
        TokenRequestType $type,
    ): TokenRequest {
        $hash = $this->tokenHasher->hash($rawToken);

        $request = $this->tokenRequestRepository->findByTokenHashAndType(
            $hash,
            $type,
        );

        if (!$request instanceof TokenRequest) {
            throw new InvalidTokenException('Invalid token not found.');
        }

        $request->consume($this->clock->now());

        $this->tokenRequestRepository->save($request, true);

        return $request;
    }

    public function consumeAnyOfTypes(
        string $rawToken,
        array $types,
    ): TokenRequest {
        foreach ($types as $type) {
            if (!$type instanceof TokenRequestType) {
                continue;
            }

            try {
                return $this->consume(
                    rawToken: $rawToken,
                    type: $type,
                );
            } catch (InvalidTokenException) {
                continue;
            }
        }

        throw new InvalidTokenException('Invalid token.');
    }
}
