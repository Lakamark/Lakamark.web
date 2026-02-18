<?php

namespace App\Foundation\Security;

use App\Foundation\Security\Contract\TokenGeneratorInterface;
use App\Foundation\Security\Exception\TokenInvalidArgumentException;
use Random\RandomException;

final readonly class TokenGeneratorService implements TokenGeneratorInterface
{
    public function __construct(
        private int $defaultBytes = 32, // 256 bits
        private int $minBytes = 16,
        private int $maxBytes = 128,
    ) {
        if ($this->minBytes < 1 || $this->maxBytes < $this->minBytes) {
            throw new TokenInvalidArgumentException('Invalid token generator bounds.');
        }
    }

    /*
     * Generates a URL-safe token (base64url, without padding).
     *
     * @throws TokenRandomException
     */
    /**
     * @throws RandomException
     */
    public function generate(?int $bytes = null): string
    {
        $bytes = $bytes ?? $this->defaultBytes;
        $bytes = $this->clamp($bytes);

        $raw = random_bytes($bytes);

        return rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
    }

    /**
     * Clamp bytes.
     */
    private function clamp(int $bytes): int
    {
        return max($this->minBytes, min($this->maxBytes, $bytes));
    }
}
