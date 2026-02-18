<?php

namespace App\Tests\Foundation\Security;

use App\Foundation\Security\TokenHasher;
use PHPUnit\Framework\TestCase;

class TokenHasherTest extends TestCase
{
    public function testHashIsDeterministicForSameToken(): void
    {
        $hasher = new TokenHasher('test-secret-token');
        $this->assertSame($hasher->hash('abc'), $hasher->hash('abc'));
    }

    public function testEqualsReturnsTrueForMatchingToken(): void
    {
        $hasher = new TokenHasher('test-secret-token');
        $hash = $hasher->hash('abc');

        $this->assertTrue($hasher->equals($hash, 'abc'));
        $this->assertFalse($hasher->equals($hash, 'nope'));
    }
}
