<?php

namespace App\Tests\Foundation;

use App\Foundation\Security\TokenGeneratorService;
use PHPUnit\Framework\TestCase;

class TokenGeneratorServiceTest extends TestCase
{
    public function testGenerateToken(): void
    {
        $tokenGenerator = new TokenGeneratorService();
        for ($i = 2; $i <= 20; ++$i) {
            $this->assertEquals($i, \mb_strlen($tokenGenerator->generateToken($i)));
        }
    }
}
