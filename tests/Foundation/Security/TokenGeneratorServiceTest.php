<?php

namespace App\Tests\Foundation\Security;

use App\Foundation\Security\TokenGeneratorService;
use PHPUnit\Framework\TestCase;
use Random\RandomException;

class TokenGeneratorServiceTest extends TestCase
{
    private const string REGEX = '/^[A-Za-z0-9\-_]+$/';

    /**
     * @throws RandomException
     */
    public function testGenerateProducesUrlSafeToken(): void
    {
        $tokenGenerator = new TokenGeneratorService();

        $token = $tokenGenerator->generate(32);

        $this->assertNotSame('', $token);
        $this->assertMatchesRegularExpression(self::REGEX, $token);
    }

    /**
     * @throws RandomException
     */
    public function testGenerateTokensAreDifferent(): void
    {
        $tokenGenerator = new TokenGeneratorService();
        $this->assertNotSame($tokenGenerator->generate(), $tokenGenerator->generate());
    }

    /**
     * @throws RandomException
     */
    public function testGenerateClampsBytes(): void
    {
        $tokenGenerator = new TokenGeneratorService(defaultBytes: 32, minBytes: 16, maxBytes: 64);

        $tokenMin = $tokenGenerator->generate(1);
        $tokenMax = $tokenGenerator->generate(9999);

        $this->assertMatchesRegularExpression(self::REGEX, $tokenMin);
        $this->assertMatchesRegularExpression(self::REGEX, $tokenMax);
    }
}
