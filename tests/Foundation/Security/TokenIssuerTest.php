<?php

namespace App\Tests\Foundation\Security;

use App\Foundation\Security\TokenGeneratorService;
use App\Foundation\Security\TokenHasher;
use App\Foundation\Security\TokenIssuer;
use PHPUnit\Framework\TestCase;
use Random\RandomException;

class TokenIssuerTest extends TestCase
{
    /**
     * @throws RandomException
     */
    public function testIssueReturnsTokenAndMatchingHash(): void
    {
        $issuer = new TokenIssuer(
            new TokenGeneratorService(),
            new TokenHasher('test-secret')
        );

        $issued = $issuer->issue(32);

        $this->assertNotSame('', $issued->token);
        $this->assertNotSame('', $issued->hash);

        $this->assertTrue((new TokenHasher('test-secret'))->equals($issued->hash, $issued->token));
    }
}
