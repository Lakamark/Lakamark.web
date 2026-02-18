<?php

namespace App\Foundation\Security;

use Random\RandomException;

/**
 * Issues a secure token and its corresponding hash.
 *
 * The plain token should be sent to the user (e.g. via email link),
 * while the hash should be stored securely in the database.
 *
 * @example
 * $issued = $issuer->issue();
 * $token  = $issued->token;
 * $hash   = $issued->hash;
 */
final readonly class TokenIssuer
{
    public function __construct(
        private TokenGeneratorService $generator,
        private TokenHasher $hasher,
    ) {
    }

    /**
     * @throws RandomException
     */
    public function issue(?int $bytes = null): GeneratedTokenDTO
    {
        $token = $this->generator->generate($bytes);

        return new GeneratedTokenDTO(
            token: $token,
            hash: $this->hasher->hash($token),
        );
    }
}
