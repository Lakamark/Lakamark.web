<?php

namespace App\Domain\Auth\Service;

use App\Domain\Auth\Entity\TokenRequest;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Enum\TokenRequestType;
use App\Domain\Auth\Repository\TokenRequestRepository;
use App\Foundation\Security\GeneratedTokenDTO;
use App\Foundation\Security\TokenIssuer;
use Random\RandomException;

readonly class TokenRequestService
{
    public function __construct(
        private TokenRequestRepository $tokenRequestRepository,
        private TokenIssuer $tokenIssuer,
    ) {
    }

    /**
     * @throws RandomException
     */
    public function issue(
        User $user,
        TokenRequestType $type,
        \DateTimeImmutable $now,
    ): GeneratedTokenDTO {
        // Revoke any existing active token for same user+type
        $this->tokenRequestRepository->revokeActiveForUserAndType(
            userId: $user->getId(),
            type: $type,
            now: $now,
        );

        // Generate a token
        $issued = $this->tokenIssuer->issue();

        // create RequestToken
        $expiresAt = $now->add($type->ttl());

        $tokenRequest = (new TokenRequest())
            ->setUser($user)
            ->setType($type)
            ->setTokenHash($issued->hash)
            ->setCreatedAt($now)
            ->setExpiresAt($expiresAt)
        ;

        $this->tokenRequestRepository->save($tokenRequest, true);

        //  Return RAW token for email link
        return $issued;
    }
}
