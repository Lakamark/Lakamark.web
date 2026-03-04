<?php

namespace App\Domain\Auth\Contract;

use App\Domain\Auth\Entity\TokenRequest;
use App\Domain\Auth\Enum\TokenRequestType;

interface TokenRequestRepositoryInterface
{
    public function save(TokenRequest $request, bool $flush = false): void;

    /**
     * Consume/revoke all non-consumed tokens for that user+type.
     */
    public function findOneByTokenHashAndType(
        string $tokenHash,
        TokenRequestType $type,
    ): ?TokenRequest;

    public function revokeActiveForUserAndType(
        int $userId,
        TokenRequestType $type,
        \DateTimeImmutable $now,
    ): int;
}
