<?php

namespace App\Foundation\Security;

use App\Foundation\Security\Contract\TokenHasherInterface;
use App\Foundation\Security\Exception\TokenInvalidArgumentException;

final readonly class TokenHasher implements TokenHasherInterface
{
    public function __construct(
        private string $secret, // inject via env: APP_TOKEN_SECRET
    ) {
        if ('' === $this->secret) {
            throw new TokenInvalidArgumentException('TokenHasher secret cannot be empty.');
        }
    }

    public function hash(string $token): string
    {
        if ('' === $token) {
            throw new TokenInvalidArgumentException('Token cannot be empty.');
        }

        return hash_hmac('sha256', $token, $this->secret);
    }

    public function equals(string $hash, string $token): bool
    {
        if ('' === $hash || '' === $token) {
            return false;
        }

        return hash_equals($hash, $this->hash($token));
    }
}
