<?php

namespace App\Domain\Auth\Contract;

use App\Domain\Auth\Entity\TokenRequest;
use App\Domain\Auth\Enum\TokenRequestType;

interface TokenRequestRepositoryInterface
{
    /**
     * Persist a token request.
     */
    public function save(TokenRequest $request, bool $flush = false): void;

    /**
     * Raw lookup by hash+type (maybe expired or consumed).
     * Useful for admin/debug/audit.
     */
    public function findByTokenHashAndType(
        string $tokenHash,
        TokenRequestType $type,
    ): ?TokenRequest;

    /**
     * Lookup a token that is valid to be consumed (not consumed + not expired).
     * This is what TokenRequestService::consume() should use.
     */
    public function findConsumableByTokenHashAndType(
        string $tokenHash,
        TokenRequestType $type,
        \DateTimeImmutable $now,
    ): ?TokenRequest;

    /**
     * Revoke all consumable tokens for that user+type (set consumedAt=now).
     * This is what TokenRequestService::issue() should call before creating a new one.
     */
    public function revokeConsumableForUserAndType(
        int $userId,
        TokenRequestType $type,
        \DateTimeImmutable $now,
    ): int;
}
