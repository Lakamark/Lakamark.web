<?php

namespace App\Domain\Auth\Contract;

use App\Domain\Auth\Entity\TokenRequest;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Enum\TokenRequestType;

interface TokenRequestRepositoryInterface
{
    /**
     * Persist a token request.
     */
    public function save(TokenRequest $tokenRequest, bool $flush = false): void;

    /**
     * Raw lookup by hash+type, even if expired/consumed/revoked.
     * Useful for audit/debug/error resolution.
     */
    public function findByTokenHashAndType(
        string $tokenHash,
        TokenRequestType $type,
    ): ?TokenRequest;

    /**
     * Lookup a usable token (not consumed, not revoked, not expired).
     * This is what TokenRequestService::consume() should use.
     *
     * @return list<TokenRequest>
     */
    public function findUsableForUserAndType(
        User $user,
        TokenRequestType $type,
        \DateTimeImmutable $now,
    ): array;
}
